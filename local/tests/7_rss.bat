@echo off

echo *** Starting 7_rss.bat ...

rem Query templates
rem /rss%ext%?source=something
rem /rss/something.xml

set folder=7_rss
call 97_working.bat

rem Positive
call	:check	rss			rss
call	:check	rss-education		rss source education.usnews.com
call	:check	rss-health		rss source health.usnews.com
call	:check	rss-money		rss source money.usnews.com
call	:check	rss-news		rss source news.usnews.com
call	:check	rss-opinion		rss source opinion.usnews.com
call	:check	rss-travel		rss source travel.usnews.com
call	:check	rss-CA			rss filter CA
call	:check	rss-news-CA		rss source news.usnews.com filter CA
call	:check	rss-CA-news		rss filter CA source news.usnews.com

rem Negative
call	:check	err-rss-1		rss1
call	:check	err-rss-source-		rss source
call	:check	err-rss-source-xxx	rss source education.usnews.com1
call	:check	err-rss-source1-	rss source1
call	:check	err-rss-source1-news	rss source1 education.usnews.com
call	:check	err-rss-sourcer1-xxx	rss source1 education.usnews.com1
call	:check	err-rss-filter-		rss filter
call	:check	err-rss-filter-xxx	rss filter xxx
call	:check	err-rss-s-news-f-xxx	rss source news.usnews.com filter CA1
call	:check	err-rss-s-xxx-f-xxx	rss source news.usnews.com1 filter CA1

set file=
set agent=
set query=
set mode=
set folder=

goto :EOF

:check
call :check_full %*
call :check_fine %*
call :check_direct %*
exit /b


rem -------------------------------------
:check_full
set query=%2%ext%
if not "%3"=="" set "query=%query%?%3"
if not "%4"=="" set "query=%query%=%4"
if not "%5"=="" set "query=%query%&%5"
if not "%6"=="" set "query=%query%=%6"
set agent=TestFull
set mode=full
set file=%1.xml
call :wget
exit /b


rem -------------------------------------
:check_fine
set query=%2
if not "%3"=="" set "query=%query%/%3"
if not "%4"=="" set "query=%query%/%4"
if not "%5"=="" set "query=%query%/%5"
if not "%6"=="" set "query=%query%/%6"
set agent=TestFine
set mode=fine
set file=%1.xml
call :wget
exit /b


rem -------------------------------------
:check_direct
set query=%2
if not "%3"=="" set "query=%query%/%3"
if not "%4"=="" set "query=%query%/%4"
if not "%5"=="" set "query=%query%/%5"
if not "%6"=="" set "query=%query%/%6"
set agent=TestDirect
set mode=direct
set file=%1.xml
call :wget
exit /b

rem -------------------------------------
:wget
call 98_folders.bat %folder% %mode%
echo "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%folder%\%mode%\%file%" "_SITE_/%query%" >> %log%
call "%wget%" -U %agent% -q -nv -a %log% -O "%output%\%folder%\%mode%\%file%" "%site%/%query%" 2>nul
call 99_compare.bat %folder%\%mode%\%file%
exit /b
