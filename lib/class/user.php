<?php

class user_helper {

	private $id = 0;
	private $username = "";
	private $password = "";
	private $logincnt = 0;
	private $email = "";
	private $ip = "";

	public function __construct($id = "") {
        if (!empty($id)) $this->Get($id);
    }

	public function SessionId($value = "") {
    	if ($value != "") $this->prop_sessionId = $value;
    	return $this->prop_sessionId;
    }
	public function Id($value = "") {
        if ($value != "") $this->id = $value;
        return $this->id;
    }
	public function LoginCnt($value = "") {
        if ($value != "") $this->logincnt = $value;
        return intval($this->logincnt);
    }
	public function Username($value = "") {
        if ($value != "") $this->username = $value;
        return $this->username;
    }
	public function Password($value = "") {
        if ($value != "") $this->password = $value;
        return $this->password;
    }
	public function IP($value = "") {
    	if ($value != "") $this->ip = $value;
    	return $this->ip;
    }
	public function Email($value = "") {
		if ($value != "") $this->email = $value;
    	return $this->email;
	}

    public function Get($id) {
    	$db = new db_helper();
        $db->CommandText("SELECT * FROM users WHERE id = %s");
        $db->Parameters($id);
        $db->Execute();
        if ($db->Rows_Count() > 0) {
        	$r = $db->Rows();
        	$this->id = $r['id'];
        	$this->username = $r['email'];
        	$this->password = $r['password'];
			$this->ip = $r['ip'];
			$this->logincnt = $r["logincnt"];
			$this->email = $r["email"];
        }
        return $db->Rows();
    }
    public function GetByEmail($email) {
    	$db = new db_helper();
        $db->CommandText("SELECT * FROM users WHERE email = '%s'");
        $db->Parameters($email);
        $db->Execute();
        if ($db->Rows_Count() > 0) {
            $r = $db->Rows();
            $this->id = $r['id'];
            $this->username = $r['email'];
            $this->password = $r['password'];
            $this->ip = $r['ip'];
            $this->logincnt = $r["logincnt"];
            $this->email = $r["email"];
        }
        return $db->Rows();
    }
    public function SetRecoverStamp() {
        if ($this->Id() > 0) {
            $db = new db_helper();
            $db->CommandText("SELECT (TIMESTAMPADD(HOUR, -1, CURRENT_TIMESTAMP) >= lastpasswordrecover) AS rcvr FROM users WHERE id = %s;");
            $db->Parameters($this->Id());
            $db->Execute();
            if ($db->Rows_Count() > 0) {
                $r = $db->Rows();
                if (intval($r["rcvr"]) == 0) return false;
            }
            $db->CommandText("UPDATE users SET lastpasswordrecover = CURRENT_TIMESTAMP WHERE id = %s;");
            $db->Parameters($this->Id());
            $db->Execute();
            return true;
        }
        return false;
    }
    /*
	public static function ResetPassword($email) {
		$randomStr = md5(microtime()); // md5 to generate the random string
	    $resultStr = substr($randomStr, 0, 8); // trim 8 digit

		$db = new db_helper();
		$db->CommandText("UPDATE users SET Password = '%s' WHERE Email = '%s' AND IsConfirmed = 1;");
		$db->Parameters(md5($resultStr));
		$db->Parameters(strip($email));
		$db->Execute();
		if ($db->Rows_Affected() > 0) {
			sendPasswordResetMail($email, $resultStr);
			return true;
		}
		return false;
	}
     *
     */
    public function PasswordChange($newpassword) {
		$db = new db_helper();
		$db->CommandText("UPDATE users SET password = '%s' WHERE id = '%s';");
		$db->Parameters(md5($newpassword));
		$db->Parameters($this->Id());
		$db->Execute();
		return true;
	}
	public static function CheckExistUsername($username) {
		$db = new db_helper();
		$db->CommandText("SELECT COUNT(Id) AS cnt FROM users WHERE username = '%s' LIMIT 1;");
		$db->Parameters($username);
		return ($db->ExecuteScalar() == 0);
	}
	public static function CheckExistEmail($email) {
		$db = new db_helper();
		$db->CommandText("SELECT COUNT(Id) AS cnt FROM users WHERE email = '%s' LIMIT 1;");
		$db->Parameters(strip($email));
		return ($db->ExecuteScalar() == 0);
	}
	public static function Login($username, $password, $ip = "") {
		global $msg;
		if (validator::isEmpty($username) || validator::isEmpty($password)) return 0;

		$api = form("api");
		
		$db = new db_helper();
		$db->CommandText("SELECT id,client_id,email,users.name,activated,active_server_id,UNIX_TIMESTAMP(joined_date) as joined_date FROM users WHERE email = '%s' AND password = '%s' AND account_disabled = 0 LIMIT 1");
		$db->Parameters($username);
		if( $api != "" ) {
			$db->Parameters($password);
		} else {
			$db->Parameters(md5($password));
		}
		$db->Execute();
		if ($db->Rows_Count() > 0) {
			$r = $db->Rows();

			$userid = $r["id"];
			$client_id = $r["client_id"];

			if( $client_id  < 0 ) {
				$msg = "Login Failed - Your account has not been correctly setup. Please contact the administrator of this website using the contact-us form.";
				return 0;
			}
			if( $r['activated'] == 0 ) {
				$msg = "Your account has not been activated yet. Please check your emails for the activation link.";
				return 0;
			}

			$_SESSION['uid'] = $userid;
			$_SESSION['client_id'] = $client_id;
			$_SESSION['username'] = $r["email"];
			$_SESSION['name'] = $r["name"];
			if( $api != "" ) {
				$_SESSION['password'] = $password;
			} else {
				$_SESSION['password'] = md5($password);
			}
			
			
			$_SESSION['joined_date'] = $r['joined_date'];
			
			
			$_SESSION['active_server_id'] = $r["active_server_id"];
			$_SESSION['active_client_id'] = getClientForServerId($r["active_server_id"]);
			$_SESSION['server_address'] = getAddressForServer($_SESSION['active_server_id']);
			$_SESSION['hash'] = getHashForClient($client_id);
			
			
			//authenticate with the application server (this may need to be changed to authenticate with all servers per this client/user)
			//$result = api_login($username, md5($password));
						
			
			$db->CommandText("UPDATE users SET ip = '%s', logincnt = logincnt + 1, login_date = CURRENT_TIMESTAMP WHERE id = %s;");
			$db->Parameters(getRealIpAddr());
			$db->Parameters($userid);
			$db->Execute();
			
			$db->CommandText("INSERT INTO users_logins (user_id,user_ip,login_headers) VALUES ('%s','%s','%s');");
			$db->Parameters($userid);
			$db->Parameters(getRealIpAddr());
			$db->Parameters(getHeadersAsJSON());
			
			$db->Execute();
			
			 
			$db = new db_helper();
			$db->CommandText("SELECT role FROM users_clients WHERE user_id = '%s' AND client_id = '%s' LIMIT 1");
			$db->Parameters($userid);
			$db->Parameters($client_id);
			$db->Execute();
			$r = $db->Rows();
			$_SESSION['role'] = $r["role"];
			
			
			return $userid;
		} else {
			$msg = "Login Failed - Username / Password Invalid. ";
			return 0;
		}
	}
	public function GeneratedCookieKey() {
		return base64_encode(base64_encode($this->username)."|".base64_encode($this->password));
	}


	public static function Register($email, $name, $subscribed, $company) {
		$db = new db_helper();
		
		$password = randomPassword(8);

		if( $subscribed == "newsletter" ) {
			$subscribed = "1";
		} else {
			$subscribed = "0";
		}
		
		$db->CommandText("SELECT id FROM users WHERE email='%s'  AND account_disabled = 0;");
		$db->Parameters($email);
		$db->Execute();
		if ($db->Rows_Affected() > 0) {
			return "The specified email address is already in the system. Try recovering your password.";
		}
		
		$activation_code = randomPassword(18);
		
		$db->CommandText("INSERT INTO users (email, password, password_temp, name, client_id,subscribed, activation_code,ip) VALUES ('%s', '%s',  '%s', '%s', -1,'%s','%s','%s');");
		$db->Parameters($email);
		$db->Parameters(md5($password));
		$db->Parameters($password);

		


		$db->Parameters(ucwords(strtolower($name)));
		$db->Parameters($subscribed);
		$db->Parameters($activation_code);
		$db->Parameters(getRealIpAddr());
		

		$db->Execute();
		if ($db->Rows_Affected() > 0) {
		
			$db->CommandText("SELECT id FROM users WHERE email = '%s' AND account_disabled=0;");
			$db->Parameters($email);
			$db->Execute();
			if ($db->Rows_Count() > 0) {
				$r = $db->Rows();
				$user_id = $r["id"];

				//Setup Company Etc
				$db->CommandText("INSERT INTO clients (client_name, contact_id, hash) VALUES ('%s', '%s',MD5('%s'));");
				$db->Parameters($company);
				$db->Parameters($user_id);
				$db->Parameters($user_id.$email);

				$db->Execute();
				if ($db->Rows_Affected() > 0) {

					$db->CommandText("SELECT client_id FROM clients WHERE contact_id='%s'; ");
					$db->Parameters($user_id);
					$db->Execute();
					if ($db->Rows_Count() > 0) {
						$r = $db->Rows();
						$client_id = $r["client_id"];

						$db->CommandText("UPDATE users SET client_id='%s' WHERE id='%s' AND client_id=-1;");
						$db->Parameters($client_id);
						$db->Parameters($user_id);
						$db->Execute();
						
						$account = form("account");
						if( $account == "selfselect" ) {
							//no account configuration
							
							
							$plan = "Account Plan: Free (No Server Configured)";
							sendAccountActivation($email, $user_id, $name, $password, $plan, $activation_code);
							
							sendRegisterAlert(ucwords(strtolower($name)), $email, "", "Self Select", "", "");
							
						} else {
							$location = form("location");
							$size_id = "1gb";
							
							$server_code = "FreeAccount-C".$client_id;
							$result = digital_ocean_provision_server($server_code, $size_id, $location);
							if( !is_object($result) ) {
							
							} else if( !property_exists( $result, 'droplet' ) ) {
							
							} else {
								$droplet_id = $result->droplet->id;
		
								$region = digital_ocean_region_name($location);
							
								$sql = "INSERT INTO servers (client_id,server_codename,server_memory,server_type,server_region,droplet_id) VALUES ('%s','%s','%s','%s','%s','%s');";
								$db = new db_helper();
								$db->CommandText($sql);
								$db->Parameters($client_id);
								$db->Parameters($server_code);
								$db->Parameters(1);
								$db->Parameters("DROPLET");
								$db->Parameters($region);
								$db->Parameters($droplet_id);
								$db->Execute();
								
								$server_id = $db->Last_Insert_ID();
								$db->CommandText("UPDATE users SET active_server_id='%s' WHERE id='%s';");
								$db->Parameters($server_id);
								$db->Parameters($user_id);
								$db->Execute();
								
								sendRegisterAlert(ucwords(strtolower($name)), $email, "", "FreeAccountServer", $memory, $region);
							}
							
							$plan = "Account Plan: Free  (1 Modeller, 0 Contributors, 1GB Cloudlet)";
							sendAccountActivation($email, $user_id, $name, $password, $plan, $activation_code);
						}
						
						
						
						
						$db->CommandText("INSERT INTO users_clients (client_id,user_id,author_user_id,role) VALUES ('%s','%s','%s','%s');");
						$db->Parameters($client_id);
						$db->Parameters($user_id);
						$db->Parameters($user_id);
						$db->Parameters('MODELLER');
						$db->Execute();
						
						
						return "";
					} else {
						//Error inserting the client, remove the user entry.

						$db->CommandText("DELETE FROM users WHERE id='%s' AND client_id=-1;");
						$db->Parameters($client_id);
						$db->Parameters($user_id);
						$db->Execute();

						return "The system encountered an error while setting up your client account (REF:2A).";

					}
				} else {
					//Error inserting the client, remove the user entry.

					$db->CommandText("DELETE FROM users WHERE id='%s' AND client_id=-1;");
					$db->Parameters($user_id);
					$db->Execute();

					return "The system encountered an error while setting up your client account.";
				}

			}
		} else {
			//echo "<!-- ".$db->ExecutedCommand()." -->";
			return "The system encountered an error while setting up your user account.";
		}
		return "";
	}

}
