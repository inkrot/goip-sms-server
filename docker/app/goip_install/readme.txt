安装步骤见GoIP_SMS_Manual.doc文件
-------
sms server update step:
1.killall goipcron
2.into "goip_install", copy "goip" to "/usr/local/goip".
3.into /usr/local/goip, copy "goipcron_m4"(if use mysql 4) or "goipcron_m5"(if use mysql 5) to /usr/local/goip/goipcron.
4.in your browser, view http://your_ip/goip/update.php to update database.
5. /usr/local/goip/run_goipcron to restart goipcron. Done!


版本更新步骤:                                                                                                     
1.关闭掉goipcron进程。                                                                                            
2.把goip文件夹复盖原来的goip文件夹。                                                                              
3.goip文件夹里的goipcron_m4(mysql4版本)或者goipcron_m5(mysql5版本)覆盖/usr/local/goip/goipcron文件。              
4.在浏览器运行http://您的IP或域名/goip/update.php进行数据库更新.                                                  
5.运行/usr/local/goip/run_goipcron，将重启goipcron服务，整个更新过程完毕。

1.13 201207
-----
增加了自动查余额和充值

1.12.4 201206
增加了上传文件发不同内容的短信
-------
goipsms v1.10.3 201105
更新内容
1.修正了Examine Sendings中发送总数量的计算错误。

-------
goipsms v1.10.2 201104
更新内容
1.如果在windows系统上启用goipcron服务出现1053错误时， 请把goip/inc/libmysql.dll文件复制到c:\windows\或者c:\windows\system32\目录再启动服务.

-------
goipsms v1.10.1 201104
更新内容
1.增加上传ussd指令的文件来发送ussd
------

goipsms v1.10 201103
更新内容
1.增加了保存通话时间到数据库（goip需要更新版本）.
2.修正了上传xml文件发送短信可能出现的错误.
3.增加了对单号码定时发送短信的功能.
-------

goipsms v1.09.1 201101
更新内容
1.修复了对https支持的错误.
-------

goipsms v1.09 201101
更新内容
1.在发送单号码情况下， 增加了一个指定线路发送短信的功能
2.在短信收件箱， 增加了查看指定goip的接收的短信的功能
3.在ussd发送记录， 增加了查看指定goip的ussd发思念过记录的功能
4.修正了一个在数据库备份时因为PHP版本兼容可能出现的问题
5.优化了数据库存取
-------

goipsms v1.08.1 200111
更新内容
1.在收件箱直接显示短信内容
-------

goipsms v1.08 201011
更新内容：
1.修正接收短信或ussd中含特殊字符时出现的保存错误
2.修正翻页时可能出现的错误


-------
goipsms 服务程序 v1.07 201010
更新内容：
1.增加一个发送短信的HTTP接口.发送中文页面为http://your_ip/dosend.php 英文页面为http://your_ip/en/dosend.php, 可以接收GET或POST参数，建议使用POST参数
  参数
  USERNAME 用户名 
  PASSWORD 用户密码
  smsnum 发送号码 
  Memo 短信内容
  smsprovider 保存在服务器上的服务商的序号(1-10)
  method 发送单个号码 固定为2


  例如：

  http://192.168.2.2/goip/en/dosend.php?USERNAME=root&PASSWORD=root&smsprovider=1&smsnum=13800138000&method=2&Memo=hello
------


goipsms 服务程序 v1.06 201010
更新内容：
1.增加ussd的数据库，增加ussd发送的保存和查询功能
2.增加ussd发送的http提交接口
3.增加向单个号码发送短信
------

goipsms 服务程序 v1.05 201008
更新内容：
1.在SIM网络服务项中增加查看和设置呼叫转移呼叫等待操作
2.goip状态参数中增加一个SIM卡号码
3.修正了goip数量超过30只能显示第一页的错误
------

goipsms 服务程序 v1.04 beta
更新内容：
1.增加接收保存短信
2.增加对goip的状态查看和管理操作
3.增加了通话记录和相应的数据库表
4.增加ussd功能
------

goipsms 服务程序 v1.03
更新内容：
1.改变绝大部分界面
2.数据库部分表的数据改变
------

goipsms 服务程序 v1.02
更新内容：
1.自动识别短信内容是否含多字节的字，以选择bit7或ucs2编码。
2.添加了发送长短信功能(仅适于SIMCOM模块,goip升级版本SMS-1.01-2.pkg以上)，自动计算长短信包含的短信条数，最多包含15条。
------
goipsms 服务程序 v1.01
更新内容：
1.dosend.php和resend.php改为后台进行，用户在关闭页面的情况下依然会完成整个过程。
2.添加一个xml文件的发送短信方式，可以定时发送和获取发送状态。格式见SMS Solution.doc文件。
3.修正一个进行大量号码的定时发送时，服务器与goip连接不能保活的错误。
4.修改了备份和导入数据库时使用的内存限制，现在可以操作更大的数据。

