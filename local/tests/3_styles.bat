@echo off

echo *** Starting 3_styles.bat ...

set folder=3_styles
call 98_folders.bat %folder%

call	:check		styles
call	:check		styles2

set result=
set folder=

goto :EOF

:check
set result=%folder%\%1.css
echo "%wget%" -q -nv -a %log% -O "%output%\%result%" "_SITE_/%1.css" >> %log%
call "%wget%" -q -nv -a %log% -O "%output%\%result%" "%site%/%1.css" 2>nul
call 99_compare.bat %result%
exit /b



