@echo off
.\compile\bamcompile imagemailer.bcp
.\compile\bamcompile mailersettings.bcp
"c:\program files\inno setup 5\iscc.exe" "%CD%\setup\imgsend.iss"
pause