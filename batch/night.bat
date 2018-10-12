rem Srovnání OKBase - AD
"C:\Program Files\PHP\v7.2\php.exe" "c:\websrv\cp\index.php" "Cron:oKBase" --verbose -cliCode "cp2018"

rem Kontrola plátců DPH - VAT
"C:\Program Files\PHP\v7.2\php.exe" "c:\websrv\cp\index.php" "Cron:verifyVATs" --verbose -cliCode "cp2018"

rem Import projektů INFOs -> Sharepoint
"C:\Program Files\PHP\v7.2\php.exe" "c:\websrv\cp\index.php" "Cron:importInfosProjects" --verbose -cliCode "cp2018"

rem Import dodavatelů INFOs -> Sharepoint
"C:\Program Files\PHP\v7.2\php.exe" "c:\websrv\cp\index.php" "Cron:importInfosVendors" --verbose -cliCode "cp2018"



rem ============================================================================
rem a uložení log filu podle data
ren "c:\websrv\cp\log\info.log" "info-%date:~0,10%.log"
ren "c:\websrv\cp\log\cron\info.log" "info-%date:~0,10%.log"
rem ren "c:\websrv\cp\log\api\info.log" "info-%date:~0,10%.log"

rem ============================================================================
rem a smažeme soubory email-sent
del "c:\websrv\cp\log\email-sent"
del "c:\websrv\cp\log\cron\email-sent"
rem del "c:\websrv\cp\log\api\email-sent"

rem ============================================================================
rem and delete old session files in session directory - older than 60 days
ForFiles /p "c:\websrv\cp\sessions" /s /d -60 /c "cmd /c del @file"
