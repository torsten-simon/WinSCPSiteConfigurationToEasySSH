 <?php

if(!@$argv[1]) {
    die("Please provide WinSCP.ini as command line argument");
}
$fileName = $argv[1];
$handle   = fopen($fileName, "r");
$sINIFile = '';
if ($handle) {
    $bKeepSection = true;
    while (($line = fgets($handle)) !== false) {
        if (preg_match('/^\[/', $line)) {
            // new section ; by default do not keep the following lines except it is session data
            if (preg_match('/^\[Sessions\\\\(.*)\\](\\s*)$/', $line, $aMatches)) {
                $bKeepSection = true;
                // As mentioned in http://php.net/manual/en/function.parse-ini-string.php,
                // some exotic characters in the section name can lead to errors
                // while reading the file. Uncomment array keys if you have read errors.
                $aRemoveForbiddenSectionChars = array(
                    '?' => '',
                    // '{' => '',
                    // '}' => '',
                    '|' => '',
                    '&' => '',
                    '~' => '',
                    '!' => '',
                    '[' => '',
                    // '(' => '',
                    // ')' => '',
                    '^' => '',
                    ']' => '',
                );
                $line                         = '[Sessions\\' . strtr($aMatches[1],
                        $aRemoveForbiddenSectionChars) . ']' . $aMatches[2];
                $sINIFile                     .= $line;
            } else {
                $bKeepSection = false;
            }
        } else {
            // process the line read. If it is site data, keep it.
            if ($bKeepSection) {
                $sINIFile .= $line;
            }
        }
    }
    
    fclose($handle);
}
$ini = parse_ini_string($sINIFile, true, INI_SCANNER_RAW);
$result = [];
foreach($ini as $section=>$data) {
    if(substr($section, 0, 9) === "Sessions\\") {
        $name = explode("/",urldecode(substr($section, 9)));
        $group = count($name) > 1 ? $name[0] : "";
        if(count($name) > 1) {
            unset($name[0]);
        }
        $name = implode("/", $name);
        $data = array_map("urldecode", $data);
        print_r($name);
        $ssh = [
            "name" => $name,
            "group" => $group,
            "host" => $data["HostName"],
            "port" => @$data["PortNumber"] ? $data["PortNumber"] : "22",
            "username" => $data["UserName"],
            "identity-file" => @$data["PublicKeyFile"]
        ];
        $result[] = $ssh;
        //print_r($ssh);
    }
}
echo "Transformed " . count($result) . " entries";
file_put_contents("hosts.json", json_encode($result, JSON_PRETTY_PRINT));