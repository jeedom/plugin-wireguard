<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class wireguard extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */


	public static function cron5() {
		foreach (self::byType('wireguard') as $eqLogic) {
			try {
				if ($eqLogic->getConfiguration('enable') == 1 && !$eqLogic->getState()) {
					if ($eqLogic->getLogicalId() == 'dnsjeedom') {
						try {
							repo_market::test();
						} catch (Exception $e) {
						}
						if (!$eqLogic->getState()) {
							$eqLogic->start_wireguard();
						}
						$eqLogic->updateState();
						return;
					}
					$eqLogic->start_wireguard();
				}
				if ($eqLogic->getConfiguration('enable') == 0 && $eqLogic->getState()) {
					$eqLogic->stop_wireguard();
				}
				$eqLogic->updateState();
			} catch (Exception $e) {
			}
		}
	}

	public static function cleanVpnName($_name) {
		return str_replace(array(' ', '(', ')', '/', ',', ';', '\\', '%', '*', '$'), '_', $_name);
	}

	/*     * *********************Méthodes d'instance************************* */

	public function updateDnsJeedomInformation() {
		if ($this->getConfiguration('PrivateKey') == '') {
			$this->setConfiguration('PrivateKey', trim(shell_exec('wg genkey')));
		}
		if ($this->getConfiguration('PresharedKey') == '') {
			$this->setConfiguration('PresharedKey', trim(shell_exec('wg genpsk')));
		}
		$publickey = trim(shell_exec('echo \'' . $this->getConfiguration('PrivateKey') . '\' | wg pubkey'));
		$url = 'https://api.eu.jeedom.link/service/jeedns/register';
		$request_http = new com_http($url);
		$request_http->setHeader(array('Content-Type: application/json'));
		$request_http->setPost(json_encode(array('username' => jeedom::getHardwareKey(), 'password' => config::byKey('dns::token'), 'PublicKey' => $publickey, 'PresharedKey' => $this->getConfiguration('PresharedKey'))));
		$result = $request_http->exec();
		$json = is_json($result, false);
		if ($json === false) {
			throw new Exception(__('Retour invalide : ', __FILE__) . $result);
		}
		if (!isset($json['state']) || $json['state'] == 'nok') {
			throw new Exception(__('Retour invalide : ', __FILE__) . json_encode($json));
		}
		$this->setConfiguration('PublicKey', $json['result']['PublicKey']);
		$this->setConfiguration('Address', $json['result']['AllowIps']);
		$this->setConfiguration('Endpoint', $json['result']['Ip'] . ':' . $json['result']['ListenPort']);
		$this->setConfiguration('AllowedIPs', '172.0.0.1/32');
		$this->setConfiguration('PeristentKeepalive', 25);
		$this->save(true);
	}

	public function getInterfaceName() {
		return 'wg_' . $this->getId();
	}

	public function getIp() {
		$result = json_decode(shell_exec("sudo ip -j -f inet addr show " . $this->getInterfaceName()), true);
		return $result[0]['addr_info'][0]['local'];
	}

	public function isUp() {
		$interface = $this->getInterfaceName();
		if ($interface === false) {
			return false;
		}
		$result = shell_exec('sudo ip addr show ' . $interface . ' 2>&1 | wc -l');
		return ($result > 1);
	}

	public function preRemove() {
		$this->stop_wireguard();
	}

	public function postSave() {
		$state = $this->getCmd(null, 'state');
		if (!is_object($state)) {
			$state = new wireguardCmd();
			$state->setLogicalId('state');
			$state->setIsVisible(1);
			$state->setName(__('Démarré', __FILE__));
			$state->setOrder(1);
			$state->setConfiguration('repeatEventManagement', 'never');
		}
		$state->setType('info');
		$state->setSubType('binary');
		$state->setEqLogic_id($this->getId());
		$state->save();

		$up = $this->getCmd(null, 'up');
		if (!is_object($up)) {
			$up = new wireguardCmd();
			$up->setLogicalId('up');
			$up->setIsVisible(1);
			$up->setName(__('Actif', __FILE__));
			$state->setOrder(2);
			$up->setConfiguration('repeatEventManagement', 'never');
		}
		$up->setType('info');
		$up->setSubType('binary');
		$up->setEqLogic_id($this->getId());
		$up->save();

		$start = $this->getCmd(null, 'start');
		if (!is_object($start)) {
			$start = new wireguardCmd();
			$start->setLogicalId('start');
			$start->setIsVisible(1);
			$start->setName(__('Démarrer', __FILE__));
			$state->setOrder(4);
		}
		$start->setType('action');
		$start->setSubType('other');
		$start->setEqLogic_id($this->getId());
		$start->save();

		$stop = $this->getCmd(null, 'stop');
		if (!is_object($stop)) {
			$stop = new wireguardCmd();
			$stop->setLogicalId('stop');
			$stop->setIsVisible(1);
			$stop->setName(__('Arrêter', __FILE__));
			$state->setOrder(5);
		}
		$stop->setType('action');
		$stop->setSubType('other');
		$stop->setEqLogic_id($this->getId());
		$stop->save();

		$ip = $this->getCmd(null, 'ip');
		if (!is_object($ip)) {
			$ip = new wireguardCmd();
			$ip->setLogicalId('ip');
			$ip->setIsVisible(1);
			$ip->setName(__('IP', __FILE__));
			$state->setOrder(3);
		}
		$ip->setType('info');
		$ip->setSubType('string');
		$ip->setEqLogic_id($this->getId());
		$ip->save();

		if ($this->getIsEnable() == 0) {
			$this->stop_wireguard();
		}
	}

	public function decrypt() {
		$this->setConfiguration('PrivateKey', utils::decrypt($this->getConfiguration('PrivateKey')));
		$this->setConfiguration('PublicKey', utils::decrypt($this->getConfiguration('PublicKey')));
		$this->setConfiguration('Endpoint', utils::decrypt($this->getConfiguration('Endpoint')));
		$this->setConfiguration('PresharedKey', utils::decrypt($this->getConfiguration('PresharedKey')));
	}
	public function encrypt() {
		$this->setConfiguration('PrivateKey', utils::encrypt($this->getConfiguration('PrivateKey')));
		$this->setConfiguration('PublicKey', utils::encrypt($this->getConfiguration('PublicKey')));
		$this->setConfiguration('Endpoint', utils::encrypt($this->getConfiguration('Endpoint')));
		$this->setConfiguration('PresharedKey', utils::encrypt($this->getConfiguration('PresharedKey')));
	}

	public static function replaceTag($_str) {
		$replace = array();
		$replace['#interface#'] = 'wg_' . $this->getId();
		return str_replace(array_keys($replace), $replace, $_str);
	}

	private function writeConfig() {
		if (!file_exists(__DIR__ . '/../../data')) {
			mkdir(__DIR__ . '/../../data');
		}
		$config = "[Interface]\n";
		$config .= "Address = " . $this->getConfiguration('Address') . "\n";
		$config .= "PrivateKey = " . $this->getConfiguration('PrivateKey') . "\n";
		if ($this->getConfiguration('PostUp') != '') {
			$config .= "PostUp = " . $this->replaceTag($this->getConfiguration('PostUp')) . "\n";
		}
		if ($this->getConfiguration('PostDown') != '') {
			$config .= "PostDown = " . $this->replaceTag($this->getConfiguration('PostDown')) . "\n";
		}
		$config .= "[Peer]\n";
		$config .= "PublicKey = " . $this->getConfiguration('PublicKey') . "\n";
		$config .= "Endpoint = " . $this->getConfiguration('Endpoint') . "\n";
		$config .= "AllowedIPs = " . $this->getConfiguration('AllowedIPs') . "\n";
		if ($this->getConfiguration('PresharedKey') != '') {
			$config .= "PresharedKey = " . $this->getConfiguration('PresharedKey') . "\n";
		}
		if ($this->getConfiguration('PeristentKeepalive') != '') {
			$config .= "PersistentKeepalive = " . $this->getConfiguration('PeristentKeepalive', 25) . "\n";
		}
		unlink(__DIR__ . '/../../data/wg_' . $this->getId() . '.conf');
		file_put_contents(__DIR__ . '/../../data/wg_' . $this->getId() . '.conf', $config);
	}

	public function getCmdLine() {
		return 'wg-quick up ' . __DIR__ . '/../../data/wg_' . $this->getId() . '.conf';
	}

	public function start_wireguard() {
		if ($this->getLogicalId() == 'dnsjeedom') {
			$this->updateDnsJeedomInformation();
		}
		$this->stop_wireguard();
		$this->writeConfig();
		$log_name = ('wireguard_' . self::cleanVpnName($this->getName()));
		log::remove($log_name);
		$cmd = system::getCmdSudo() . $this->getCmdLine() . ' >> ' . log::getPathToLog($log_name) . '  2>&1 &';
		log::add($log_name, 'info', __('Lancement wireguard : ', __FILE__) . $cmd);
		shell_exec($cmd);
		$this->updateState();
		if ($this->getLogicalId() == 'dnsjeedom') {
			$interface = $this->getInterfaceName();
			if ($interface !== null && $interface != '' && $interface !== false) {
				$cmd = system::getCmdSudo() . 'iptables -L INPUT -v --line-numbers | grep ' . $interface;
				log::add('wireguard', 'debug', $cmd);
				$rules = shell_exec($cmd);
				$c = 0;
				while ($rules != '') {
					$ln = explode(" ", explode("\n", $rules)[0])[0];
					if ($ln == '') {
						break;
					}
					$cmd = system::getCmdSudo() . 'iptables -D INPUT ' . $ln;
					log::add('wireguard', 'debug', $cmd);
					shell_exec($cmd);
					$rules = shell_exec(system::getCmdSudo() . 'iptables -L INPUT -v --line-numbers | grep ' . $interface);
					$c++;
					if ($c > 25) {
						break;
					}
				}
				$cmd = system::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -p tcp  --destination-port 80 -j ACCEPT';
				log::add('wireguard', 'debug', $cmd);
				shell_exec($cmd);
				if (config::byKey('dns::openport') != '') {
					foreach (explode(',', config::byKey('dns::openport')) as $port) {
						if (is_nan($port)) {
							continue;
						}
						try {
							$cmd = system::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -p tcp  --destination-port ' . $port . ' -j ACCEPT';
							log::add('wireguard', 'debug', $cmd);
							shell_exec($cmd);
						} catch (Exception $e) {
						}
					}
				}
				$cmd = system::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -j DROP';
				log::add('wireguard', 'debug', $cmd);
				shell_exec($cmd);
			}
		}
	}

	public function stop_wireguard() {
		shell_exec('sudo wg-quick down ' . __DIR__ . '/../../data/wg_' . $this->getId() . '.conf');
		$this->updateState();
	}

	public function getState() {
		if ((strtotime('now')  - $this->getLatestHandshakes()) < 200) {
			return true;
		}
		sleep(5);
		if ((strtotime('now')  - $this->getLatestHandshakes()) < 200) {
			return true;
		}
	}

	public function getLatestHandshakes() {
		return explode("\t", shell_exec("sudo wg show " . $this->getInterfaceName() . " latest-handshakes"))[1];
	}

	public function updateState() {
		$cmd = $this->getCmd('info', 'state');
		if (is_object($cmd)) {
			if ($this->getState()) {
				$cmd->event(1);
			} else {
				$cmd->event(0);
			}
		}
		$up = $this->isUp();
		if ($up) {
			$ip = $this->getIp();
		} else {
			$ip = __('Aucune', __FILE__);
		}
		$this->checkAndUpdateCmd('up', $up);
		$this->checkAndUpdateCmd('ip', $ip);
	}

	/*     * **********************Getteur Setteur*************************** */
}

class wireguardCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'start') {
			$eqLogic->start_wireguard();
			if ($eqLogic->getConfiguration('enable') == 0) {
				$eqLogic->setConfiguration('enable', 1);
				$eqLogic->save(true);
			}
		}
		if ($this->getLogicalId() == 'stop') {
			$eqLogic->stop_wireguard();
			if ($eqLogic->getConfiguration('enable') == 1) {
				$eqLogic->setConfiguration('enable', 0);
				$eqLogic->save(true);
			}
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}
