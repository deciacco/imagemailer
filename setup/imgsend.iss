[Setup]
AppName=Image Mailer
AppVerName=Image Mailer v1.0 Beta
DefaultDirName={pf}\Deciacco.com\ImageMailer
DefaultGroupName=Deciacco.com\Image Mailer
UninstallDisplayIcon={app}\imgicon.ico
InfoBeforeFile=
OutputDir=c:\Documents and Settings\ecilento\desktop\phpapps\Dev\Apps\imagemailer\setup
MinVersion=0,5.01.2600
LicenseFile=C:\Documents and Settings\ecilento\Desktop\phpapps\Dev\Apps\imagemailer\setup\license.txt
DisableProgramGroupPage=true
AppCopyright=Copyright © 2009 Deciacco.com
DisableReadyPage=true
ShowLanguageDialog=no
SourceDir=c:\Documents and Settings\ecilento\desktop\phpapps\Dev\Apps\imagemailer
DisableDirPage=true
AppendDefaultGroupName=false
UsePreviousGroup=false
AppPublisher=DeCiacco.com
AppPublisherURL=http://www.deciacco.com/blog/php/easily-email-images-with-microsoft-outlook
AppSupportURL=http://www.deciacco.com/blog/php/easily-email-images-with-microsoft-outlook
AppUpdatesURL=http://www.deciacco.com/blog/php/easily-email-images-with-microsoft-outlook
AppID={{B26AD6F3-B561-48D5-ABE3-7136033CC99F}
OutputBaseFilename=imgsendsetup

[Files]
Source: imagemailer\imgsend.exe; DestDir: {app}
Source: imagemailer\imgsend.ini; DestDir: {app}
Source: imagemailer\imgsend.exe.manifest; DestDir: {app}
Source: imagemailer\imgicon.ico; DestDir: {app}
Source: mailersettings\mailersettings.exe; DestDir: {app}
Source: mailersettings\mailersettings.exe.manifest; DestDir: {app}
Source: mailersettings\otheroptions.ico; DestDir: {app}
Source: imagemailer\htmltemplate.htm.sample; DestDir: {app}

[Icons]
;Name: {group}\Image Resizer; Filename: {app}\launch_imageresizer.exe; IconIndex: 0; WorkingDir: {app}; IconFilename: {app}\launch_imageresizer.exe
Name: {sendto}\Image Mailer for Outlook; Filename: {app}\imgsend.exe; IconIndex: 0
Name: {group}\{cm:UninstallProgram,ImageMailer}; Filename: {uninstallexe}
Name: {group}\Settings; Filename: {app}\mailersettings.exe
