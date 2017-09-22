@echo off

%~d0
cd "%~dp0"

set ROOT_DIR="%~dp0"
set CYG_DIR="%~dp0web\cygwin"
set DATA_DIR="%~dp0data"

echo ROOT_DIR == %ROOT_DIR%
echo CYG_DIR == %CYG_DIR%
echo DATA_DIR == %DATA_DIR%


start web\cygwin\bin\bash /uninstall.sh



