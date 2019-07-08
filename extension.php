<?php

class MicropubLikeExtension extends Minz_Extension
{
    public function init() {
	$current_user = Minz_Session::param('currentUser');
        $filename =  'micropub.' . $current_user . '.json';
        $staticPath = join_path($this->getPath(), 'static');
        $filepath = join_path($staticPath, $filename);
	
	if (file_exists($filepath)) {
		$config = json_decode(file_get_contents($filepath), TRUE);
		if (isset($config["endpoint"]) && isset($config["token"])) {
                	$this->micropub_endpoint = htmlentities($config["endpoint"]);
                	$this->micropub_token = htmlentities($config["token"]); 
        		$this->registerHook('entry_after_favourite', array($this, 'syndicateFavourite'));
		}
	}
    }
    public function syndicateFavourite($id, $is_favourite = 1) {
	    $endpoint = $this->micropub_endpoint;
	    $token= $this->micropub_token;
	    if ($is_favourite) {
		    $entryDAO = FreshRSS_Factory::createEntryDao();
		    $entry = $entryDAO->searchById($id);
		    $link = $entry->link();
		    # Create a JSON "like" using the micropub syntax.
		    $data = array(
		    	"type" => array("h-entry"),
		        "properties" => array (
				"like-of" => array($link),
				 ),
			 );
		    $body = json_encode($data);
		    $ch = curl_init($endpoint);
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    	'Content-Type: application/json',
    			'Content-Length: ' . strlen($body),
    			'Authorization: Bearer ' . $token
			)
		    ); 
		    $result = curl_exec($ch);
		    curl_close($ch);
	    }
	    
    }
    public function handleConfigureAction() {
                $this->registerTranslates();

                $current_user = Minz_Session::param('currentUser');
                $filename =  'micropub.' . $current_user . '.json';
                $staticPath = join_path($this->getPath(), 'static');
                $filepath = join_path($staticPath, $filename);

                if (!file_exists($filepath) && !is_writable($staticPath)) {
                        $tmpPath = explode(EXTENSIONS_PATH . '/', $staticPath);
                        $this->permission_problem = $tmpPath[1] . '/';
                } else if (file_exists($filepath) && !is_writable($filepath)) {
                        $tmpPath = explode(EXTENSIONS_PATH . '/', $filepath);
                        $this->permission_problem = $tmpPath[1];
                } else if (Minz_Request::isPost()) {
                        $endpoint = html_entity_decode(Minz_Request::param('micropub-endpoint', ''));
                        $token = html_entity_decode(Minz_Request::param('micropub-token', ''));
			$config = json_encode(array(
				"endpoint" => $endpoint,
				"token" => $token,
			));
                        file_put_contents($filepath, $config);
                }

                $this->micropub_endpoint = '';
                $this->micropub_token= '';
                if (file_exists($filepath)) {
			$config = json_decode(file_get_contents($filepath), TRUE);
                        $this->micropub_endpoint = htmlentities($config["endpoint"]);
                        $this->micropub_token = htmlentities($config["token"]);
                }
        }

}

