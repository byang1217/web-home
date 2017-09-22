@echo off

%~d0
cd "%~dp0"

set ROOT_DIR="%~dp0"
set CYG_DIR="%~dp0tools\cygwin"
set DATA_DIR="%~dp0data"

echo ROOT_DIR == %ROOT_DIR%
echo CYG_DIR == %CYG_DIR%
echo DATA_DIR == %DATA_DIR%


start tools\cygwin\bin\bash /install.sh



