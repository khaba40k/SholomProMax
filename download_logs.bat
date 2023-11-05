C:
cd C:/Users/timoh/AppData/Local/Programs/WinSCP
winscp.com /command ^
    "open ftp://andrii@sholompromax.com:Andrii01_@sholompromax.com/" ^
    "lcd E:\ftp_local\SholomProMax\logs" ^
    "get -delete error_log *1.txt" ^
    "cd /blok" ^
    "get -delete error_log *2.txt" ^
    "exit"