<?php
class DNSOperator {
	private $user;
	private $fetcher;
	private $mode;
	private $fetch_url;
	//使用fetchurl模式时用于临时存储地址。
	const USER_AGENT = 'empod/1.0(eikeime@gmail.com)';
	//url 列表
	const DOMAIN_INFO = 'https://dnsapi.cn/Domain.Info';
	const DOMAIN_LIST = 'https://dnsapi.cn/Domain.List';
	const RECORD_LIST = 'https://dnsapi.cn/Record.List';
	const RECORD_DDNS = 'https://dnsapi.cn/Record.Ddns';
	const RECORD_CREATE = 'https://dnsapi.cn/Record.Create';
	const RECORD_MODIFY = 'https://dnsapi.cn/Record.Modify';
	const RECORD_INFO = 'https://dnsapi.cn/Record.Info';

	/*
	 * 初始化类
	 * @param string $login_email 	登陆账号
	 * @param string $password 		登录密码
	 * @param string $mode 			curl或者fetchurl
	 * @param string $format 		返回数据类型 json 或者 xml 默认json
	 * @param string $lang  		返回语言 默认cn
	 */

	public function __construct($login_email, $password, $mode = 'curl', $format = 'json', $lang = 'cn') {
		$this -> user = array('login_email' => $login_email, 'login_password' => $password, 'format' => $format, 'lang' => $lang);
		$this -> mode = $mode;
		if ($this -> mode == "curl") {
			$this -> fetcher = curl_init();
			curl_setopt($this -> fetcher, CURLOPT_USERAGENT, self::USER_AGENT);
			curl_setopt($this -> fetcher, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this -> fetcher, CURLOPT_POST, 1);
		} else if ($this -> mode == "fetchurl") {
			$this -> fetcher = new SaeFetchurl();
		}
	}

	private function prepare($url, $data = array()) {
		if ($this -> mode == "curl") {
			curl_setopt($this -> fetcher, CURLOPT_URL, $url);
			curl_setopt($this -> fetcher, CURLOPT_POSTFIELDS, array_merge($this -> user, $data));
		} else if ($this -> mode == "fetchurl") {
			$this -> fetcher -> clean();
			$this -> fetcher -> setMethod('post');
			$this -> fetch_url = $url;
			$this -> fetcher -> setPostData(array_merge($this -> user, $data));
		}
	}

	private function execute() {
		if ($this -> mode == "curl") {
			return json_decode(curl_exec($this -> fetcher), TRUE);
		} else if ($this -> mode == "fetchurl") {
			return json_decode($this -> fetcher -> fetch($this -> fetch_url, array('useragent' => self::USER_AGENT)), true);
		}
	}

	public function getErrno() {
		if ($this -> mode == "curl") {
			return 'curl error no:' . curl_errno($this -> fetcher);
		} else if ($this -> mode == "fetchurl") {
			return 'saefetchurl error no:' . $this -> fetcher -> errno();
		}
	}

	public function __destruct() {
		if ($this -> mode == "curl") {
			curl_close($this -> fetcher);
		} else if ($this -> mode == "fetchurl") {

		}
	}

	//domain
	public function domainList() {
		$this -> prepare(self::DOMAIN_LIST);
		return $this -> execute();
	}

	public function domainInfo($domain, $byid = false) {
		if ($byid == FALSE) {
			$this -> prepare(self::DOMAIN_INFO, array('domain' => $domain));
		} else {
			$this -> prepare(self::DOMAIN_INFO, array('domain_id' => $domain));
		}
		return $this -> execute();
	}

	//recordlist
	public function recordList($domainId, $subDomain = null) {
		$data = array('domain_id' => $domainId);
		if (!empty($subDomain)) {
			$data['sub_domain'] = $subDomain;
		}
		$this -> prepare(self::RECORD_LIST, $data);
		return $this -> execute();
	}

	public function recordDdns($domainId, $recordId, $subDomain, $line = '默认') {
		$data = array('domain_id' => $domainId, 'record_id' => $recordId, 'sub_domain' => $subDomain, 'record_line' => $line);
		$this -> prepare(self::RECORD_DDNS, $data);
		return $this -> execute();
	}

	public function recordCreate($domainId, $subDomain, $type, $value, $line = '默认', $ttl = 600) {
		$data = array('domain_id' => $domainId, 'sub_domain' => $subDomain, 'record_type' => $type, 'record_line' => $line, 'value' => $value, 'ttl' => $ttl);
		$this -> prepare(self::RECORD_CREATE, $data);
		return $this -> execute();
	}

	public function recordModify($domainId, $recordId, $subDomain, $type, $value, $line = '默认', $ttl = 600) {
		$data = array('domain_id' => $domainId, 'record_id' => $recordId, 'sub_domain' => $subDomain, 'record_type' => $type, 'record_line' => $line, 'value' => $value, 'ttl' => $ttl);
		$this -> prepare(self::RECORD_MODIFY, $data);
		return $this -> execute();
	}

	public function recordInfo($domainId, $recordId) {
		$this -> prepare(self::RECORD_INFO, array('domain_id' => $domainId, 'record_id' => $recordId));
		return $this -> execute();
	}

}
?>