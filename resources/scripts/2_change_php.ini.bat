@echo off
set old=%1
set new=%2
find /c "xdebug.start_with_request=%old%" "C:\xampp\php\php.ini" >NUL
if %errorlevel% equ 1 goto notfound
  Rem  3_change_php.ini.bat %old% %new%
	wsl sed -i "s/xdebug.start_with_request=%old%/xdebug.start_with_request=%new%/" /mnt/c/xampp/php/php.ini
	C:\xampp\htdocs\slim-example-project\resources\scripts\3_restart_apache_as_admin_shortcut.lnk
	goto done
:notfound
Rem    echo notfound
goto done
:done


