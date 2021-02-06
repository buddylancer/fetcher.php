@echo off

echo *** Starting 8_methods.bat ...

rem Call templates
rem ID		Method	Par1	Par2	Par3	...
rem (1)		getById	1


set folder=8_methods

rem -----------------------------------------------
set package=bula-fetcher-model
call 98_folders.bat %folder% %package%


set class=DOCategory

set result=OK
call	:check	(AZ)		getCategoryById AZ
call	:check	(WY)		getCategoryById WY
call	:check	(Arizona)	getCategoryByName Arizona
call	:check	(Wyoming)	getCategoryByName Wyoming
call	:check	()		enumCategories
call	:check	(counter)	enumCategories counter
call	:check	(counter~5)	enumCategories counter 5
call	:check	(counter~5~10)	enumCategories counter 5 10

set result=ERR
call	:check	()		getCategoryById
call	:check	(XX)		getCategoryById XX
call	:check	()		getCategoryByName
call	:check	(xxx)		getCategoryByName xxx
call	:check	(xxx~-5)	enumCategories counter -5
call	:check	(xxx~-5~-10)	enumCategories counter -5 -10


set class=DOItem

set result=OK
call	:check	(1)		getById 1
call	:check	(545)		getById 545
call	:check	(S1)		findItemByLink https://www.usnews.com/education/articles/2007/08/28/sat-scores-drop-for-the-second-year-in-a-row
call	:check	(S545)		findItemByLink https://travel.usnews.com/features/the-best-camping-gear-to-buy-for-your-next-adventure
rem call:check	S		item-build-filter	buildSqlFilter in1|in2~ex1|ex2
call	:check	(S~~1~25)	enumItems news.usnews.com _ 1 25
rem call:check	(D)		enumItemsFromDate 2020-03-28
call	:check	(D~S~S~20)	enumItemsFromSource 2020-03-28 news.usnews.com _ 20
rem call:check	()		purgeOldItems

set result=ERR
call	:check	()		getById
call	:check	(0)		getById 0
call	:check	(-1)		getById -1
call	:check	(999999)	getById 999999
call	:check	(xxx)		getById xxx
call	:check	()		findItemByLink
call	:check	(xxx)		findItemByLink incorrect-link

set class=DOSource

set result=OK
call	:check	()		enumSources
call	:check	()		enumFetchedSources
call	:check	()		enumSourcesWithCounters
call	:check	(10)		getSourceById 10
call	:check	(S)		getSourceByName news.usnews.com
call	:check	(S)		checkSourceName news.usnews.com

set result=ERR
call	:check	()		getSourceById
call	:check	(0)		getSourceById 0
call	:check	(-1)		getSourceById -1
call	:check	(999999)	getSourceById 999999
call	:check	(xxx)		getSourceById xxx
call	:check	()		getSourceByName
call	:check	(xxx)		getSourceByName xxx
call	:check	(xxx)		checkSourceName xxx


set file=
set folder=
set result=
set class=
set package=

goto :EOF

:check
set "query=call%ext%?code=%code%&package=%package%&class=%class%"
if not "%2"=="" set "query=%query%&method=%2"
if not "%3"=="" set "query=%query%&par1=%3"
if not "%4"=="" set "query=%query%&par2=%4"
if not "%5"=="" set "query=%query%&par3=%5"
if not "%6"=="" set "query=%query%&par4=%6"
if not "%7"=="" set "query=%query%&par5=%7"
if not "%8"=="" set "query=%query%&par6=%8"
if not "%9"=="" set "query=%query%&par7=%9"
set "file=%class%.%2%1.%result%.html"
call :wget
exit /b

rem -------------------------------------
:wget
echo "%wget%" -q -nv -a %log% -O "%output%\%folder%\%package%\%file%" "_SITE_/Testing/%query%" >> %log%
call "%wget%" -q -nv -a %log% -O "%output%\%folder%\%package%\%file%" "%site%/Testing/%query%" 2>nul
call 99_compare.bat %folder%\%package%\%file%
exit /b
