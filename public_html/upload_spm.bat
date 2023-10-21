C:
cd C:/Users/timoh/AppData/Local/Programs/WinSCP
winscp.com /command ^
    "open ftp://andrii@sholompromax.com:Andrii01_@sholompromax.com/" ^
    "lcd E:\ftp_local\SholomProMax" ^
    "put index.php" ^
    "exit"
start http://sholompromax.com