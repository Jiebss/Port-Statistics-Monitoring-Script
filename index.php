<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Simple monitoring tool using SNMP v2c</title>
  </head>

  <body>
    <div id="body">
      <?php
		try {
			$host    = 'HOSTHERE';
			$db      = 'DBNAMEHERE';
			$user    = 'USERNAMEHERE';
			$pass    = 'PASSWORDHERE';
			$charset = 'utf8';
			$dsn  = "mysql:host=$host;dbname=$db;charset=$charset";
			$opt  = [
						PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES   => false,
					  ];
			$pdo  = new PDO($dsn, $user, $pass, $opt);
		}
		catch (PDOexception $e) {
			echo "Unable to connect to DB: " . $e->getMessage() . "";
		}

        try {
          $stmt = $pdo->prepare("SELECT deviceid, ipaddress FROM devices");
          $stmt->execute();
          $data = array();
          $deviceid = array();
          if($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch()) {
              $deviceid[] = $row['deviceid'];
              $data[]   = $row['ipaddress'];
            }
            $result = array_combine($deviceid, $data);
            foreach($result as $key => $value) {
              $id = $key;
              $session = new SNMP(SNMP::VERSION_2c, "$value", "DBNAMEHERE");
              $session->valueretrieval = SNMP_VALUE_PLAIN;
              $ifhost   = $session->get(".iso.org.dod.internet.mgmt.mib-2.interfaces.ifTable.ifEntry.ifDescr.234889216", TRUE);
              $ifname   = $session->walk(".iso.org.dod.internet.mgmt.mib-2.ifMIB.ifMIBObjects.ifXTable.ifXEntry.ifName", TRUE);
              foreach ($ifname as $naam => $ioid) {
                echo "" . $naam . " -/- ";
                echo "" . $ioid . "</br>"; 
                try {
                  $stmt = $pdo->prepare("INSERT INTO ports (devicename, interfacename, interfaceoid, deviceid) VALUES (:devicename, :interfacename, :interfaceoid, :deviceid)
                                            ON DUPLICATE KEY UPDATE interfaceoid = :test");
                  $stmt->execute(array(":devicename" => $ifhost, ":interfacename" => $ioid, ":interfaceoid" => $naam, ":test" => $naam, ":deviceid" => $id));
                }
                catch (PDOException $e) {
                  echo "Something went wrong: " . $e->getMessage() . "";
                }
                $session->close();
              }

              try {
                $stmt = $pdo->prepare("SELECT interfaceoid FROM ports WHERE deviceid = :deviceid");
                $stmt->execute(array(":deviceid" => $id));
                $combi = [];
                if($stmt->rowCount() > 0) {
                  while($row = $stmt->fetch()) {
                    if (!isset($combi[$value])) {
                      $combi[$value] = [];
                    }
                    $combi[$value][] = $row['interfaceoid'];
                  }

                  foreach($combi as $ip => $oid) {
                    $session = new SNMP(SNMP::VERSION_2c, "$ip", "aQuestora");
                    $session->valueretrieval = SNMP_VALUE_PLAIN;
                    
                    foreach($oid as $bla) {
                      $ifinerrorsoid      = ".iso.org.dod.internet.mgmt.mib-2.interfaces.ifTable.ifEntry.ifInErrors.$bla";
                      $queryifinerrors    = $session->get("$ifinerrorsoid", TRUE);
                      $ifhighspeedoid     = ".iso.org.dod.internet.mgmt.mib-2.ifMIB.ifMIBObjects.ifXTable.ifXEntry.ifHighSpeed.$bla";
                      $queryifhighspeed   = $session->get("$ifhighspeedoid", TRUE);
                      try {
                        $datum = date("Y-m-d H:i:s");
                        $stmt = $pdo->prepare("INSERT INTO statistics (erroroid, interfaceerror, highspeedoid, ifhighspeed, time, portid) VALUES (:erroroid, :interfaceerror, :highspeedoid, :ifhighspeed, :time, (SELECT id FROM ports WHERE deviceid = :deviceid AND interfaceoid = :interfaceoid))");
                        $stmt->execute(array(":erroroid" => $ifinerrorsoid, ":interfaceerror" => $queryifinerrors,  ":highspeedoid" => $ifhighspeedoid, ":ifhighspeed" => $queryifhighspeed, ":time" => $datum, ":deviceid" => $id, ":interfaceoid" => $bla));
                      }
                      catch(PDOException $e) {
                        echo "Iets gaat fout: " . $e->getMessage() . "";
                      }
                    }
                  }
                }
              }
              catch(PDOException $e) {
                echo "Selecteren van interfaceoid ging verkeerd: " . $e->getMessage() . "";
              }
            }
          }
          else {
            echo "Geen apparaten aanwezig.";
          }
        }
        catch (PDOException $e) {
          echo "Something went wrong: " . $e->getMessage() . "";
        }
      ?>
    </div>
  </body>
</html>