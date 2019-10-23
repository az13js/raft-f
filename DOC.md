# DOC

## 在终端启动队列消费

    php public/cli.php queue:worker --tube=tube_name --config_key=key_name --memory_limit=100000000

`memory_limit`的单位是字节。

## 状态

    # 持久状态（所有服务器都有的）。在RPC之前保存完更新内容。
    currentTerm   # 初始化为0，单调递增
    votedFor      # 接收到投票时的候选人ID，没有为空
    log[]         # 所有日志实体，每个实体包含应用于状态机的命令，当接收到领导者的实体时term增加。第一个索引是1。
    # 易失状态（所有服务器都有的）
    commitIndex   # 已提交的最大的日志实体的索引。初始化为0，单调递增。
    lastApplied   # 已应用到状态机的最大的实体索引，初始化为0，单调递增。
    # 易失状态（领导者服务器）。在选举完成的时候重新初始化。
    nextIndex[]   # 保存将要发送给每一个服务器的下一个日志实体的索引。初始化为领导者最大的日志索引+1。
    matchIndex[]  # 保存已同步到每一个服务器的最大日志索引。初始化为0，单调递增。

## 追加实体RPC

由领导者调用用来复制日志实体。同样也用于心跳检测。

参数：

    term          # 领导者的term
    leaderId      # 跟随者可以根据这个重定向客户端
    prevLogIndex  # 紧接着的新的一个日志实体的索引
    prevLogTerm   # prevLogIndex指向的实体的term
    entries[]     # 需要保存的日志实体，心跳请求时为空，为了提升效率可能发送多个
    leaderCommit  # 领导者的commitIndex

返回结果

    term          # currentTerm，用来给领导者更新它自己的状态
    success       # 如果跟随者包含prevLogIndex和prevLogTerm的实体，那么返回true

接受者实现的：

1. `trem < currentTerm`那么返回false。
2. 如果term匹配到prevLogTerm的，日志不包含prevLogIndex的内容，返回false。
3. 如果存在的实体与新的实体冲突，那么删除存在的实体并且全部跟随它。
4. 日志中不存在那么把新的实体追加。
5. 如果`leaderCommit > commitIndex`，设置`commitIndex = min(leaderCommit,最后一个新的实体的索引)`

## 请求选举的RPC

由候选人调用，用于发起选举

参数：

    term           # 候选人的term
    candidateId    # 候选人请求发起的选举
    lastLogIndex   # 候选人最后一个日志实体的索引
    lastLogTerm    # 候选人最后一个日志实体的term

返回值：

    term           # currentTerm，用于给候选人更新它自己
    voteGranted    # 这个为true意味着候选人接受选举

接收者的实现

1. 如果`term < currentTerm`那么返回false
2. 如果投票目标是空或者是candidateId，以及候选人的最后一个日志是更新到跟接受者的日志一样，那么同意投票。

## 用于服务器的规则

对于所有的服务器

1. 如果`commitIndex > lastApplied`，增加lastApplied，应用log[lastApplied]到状态机。
2. 如果 RPC 请求或响应包含term`T > currentTerm`，那么设置`currentTerm = T`，将自己转变为跟随者。

对于跟随者

1. 响应领导者和候选人的RPC。
2. 如果选举超时未收到现任领导人的`AppendEntries`的RPC或候选人的发起投票，那么将自己转换为候选人

候选人

1. 一旦转换为候选人，就开始选举过程。
- 增加currentTerm
- 投票给自己
- 重设选举时间
- 发送请求投票的RPC给所有其他的服务器
2. 如果收到大多数服务器的投票，那么自己变成领导者
3. 如果收到新的领导者的AppendEntries RPC，那么自己变成跟随者
4. 如果选举时间超时，那么重新发起选举

领导者

1. 当选时：发送初始化的空AppendEntries RPC心跳给所有的服务器，在空闲期间重复，以防止选举超时
2. 如果接收到客户端的命令：插入实体到本地日志，在实体应用到状态机后给客户端响应。
3. 如果最后一个日志的索引`>=nextIndex`中的任何一个跟随者，那么：从`nextIndex`开始发送AppendEntries RPC，把日志实体携带上去：
- 如果成功，更新对应跟随者的nextIndex和matchIndex
- 如果因为不一致而导致AppendEntries失败，那么：递减nextIndex并重试。
4. 如果存在N，`N > commitIndex`，大部分的`matchIndex[i] ≥ N`以及`log[N].term == currentTerm`，那么设置`commitIndex = N`。

## 实现到frame里面

1. 启动系统的时候每台都是跟随者，各自在缓存设置一个`last_call_time`，并将这个时间一起推送到队列任务，设置随机的超时时间。任务被消费的时候，检查任务内记录的`last_call_time`与缓存里面的`last_call_time`，如果缓存的时间晚于内部的时间，那么任务完成；如果缓存的时间早于或等于内部的时间，那么将自己变成候选人并进入选举阶段。当每台服务器收到心跳的时候，也设置缓存的`last_call_time`，执行推送到消息队列的操作。这一点是用Beanstalk精确实现Raft的超时触发的过程。
2. 第一点可封装成：定时器可以新建，后面建立的会将前面的未执行任务取消，新建的时候指定延时和任务回调。