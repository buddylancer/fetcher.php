@echo off

echo *** Starting 1_create.bat...

set folder=1_create
call 98_folders.bat %folder%

set mysql=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe
set dbname=dbusnews
set dbuser=dbusnews
set dbpass=dbusnews

set mysql_with_user="%mysql%" --user=%dbuser% --password=%dbpass%

set result=%folder%\log.txt
set log1=%output%\%result%
echo > %log1%

%mysql_with_user% -v -e "drop database %dbname%"
%mysql_with_user% -v -e "create database %dbname%" >>%log1%
rem %mysql_with_user% -v -e "grant all on %dbname%.* to %dbuser%@localhost identified by '%dbpass%'" >>%log1%
%mysql_with_user% -v -e "source %input%\create_%dbname%.sql" %dbname% >>%log1%
%mysql_with_user% -v -e "source %input%\load_%dbname%.sql" %dbname% >>%log1%

call 99_compare.bat %result%

set log1=
set mysql_with_user=
set dbpass=
set dbuser=
set dbname=
set mysql=
set result=
set folder=
