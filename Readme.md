Transform a WinSCP.ini file to [EasySSH](https://github.com/muriloventuroso/easyssh)

Inspired by: 
https://github.com/Mikaciu/WinSCPSiteConfigurationToFileZilla

Only the following fields are transformed:
- Group
- Name
- Hostname
- Port
- Username
- Keyfile-Location

Passwords wont' be transformed.

Usage:

`php transform.php WinSCP.ini`