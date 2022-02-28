@echo off

if not exist "%USERPROFILE%\「开始」菜单\程序\goipsms" md "%USERPROFILE%\「开始」菜单\程序\goipsms"

sc stop goipcron
sc delete goipcron
sc create goipcron binpath= "%cd%\goipcron.exe %cd%\goipcronerr.log %cd%\config.inc.php"
sc config goipcron start= AUTO
echo sc start goipcron > "%USERPROFILE%\「开始」菜单\程序\goipsms\goipcron start.cmd"
echo @pause >> "%USERPROFILE%\「开始」菜单\程序\goipsms\goipcron start.cmd"
echo sc stop goipcron > "%USERPROFILE%\「开始」菜单\程序\goipsms\goipcron stop.cmd"
echo @pause >> "%USERPROFILE%\「开始」菜单\程序\goipsms\goipcron stop.cmd"

set shortCutPath="%USERPROFILE%\「开始」菜单\程序\goipsms\goipsms install.lnk" 
echo Dim WshShell,Shortcut>>tmp.vbs 
echo Dim path,fso>>tmp.vbs 
echo path="%~dp0install.cmd">>tmp.vbs 
echo Set fso=CreateObject("Scripting.FileSystemObject")>>tmp.vbs 
echo Set WshShell=WScript.CreateObject("WScript.Shell")>>tmp.vbs 
echo Set Shortcut=WshShell.CreateShortCut(%shortCutPath%)>>tmp.vbs 
echo Shortcut.TargetPath=path>>tmp.vbs 
echo Shortcut.Save>>tmp.vbs 
"%SystemRoot%\System32\WScript.exe" tmp.vbs 
@del /f /s /q tmp.vbs 

set shortCutPath="%USERPROFILE%\「开始」菜单\程序\goipsms\goipsms uninstall.lnk" 
echo Dim WshShell,Shortcut>>tmp.vbs 
echo Dim path,fso>>tmp.vbs 
echo path="%~dp0uninstall.cmd">>tmp.vbs 
echo Set fso=CreateObject("Scripting.FileSystemObject")>>tmp.vbs 
echo Set WshShell=WScript.CreateObject("WScript.Shell")>>tmp.vbs 
echo Set Shortcut=WshShell.CreateShortCut(%shortCutPath%)>>tmp.vbs 
echo Shortcut.TargetPath=path>>tmp.vbs 
echo Shortcut.Save>>tmp.vbs 
"%SystemRoot%\System32\WScript.exe" tmp.vbs 
@del /f /s /q tmp.vbs 

sc start goipcron 
echo start goipcron
@pause