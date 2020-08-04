<?php
interface ICrawl
{
	public function Crawl();
	public function GetChildURL($URL);
	public function SearchDOMBody($LinkObject);
	public function followLink($url, $depth);
	public function convertLink($site, $path);
	public function ignoreLink($url);
	public function insertIntoDatabase($link, $depth);
}

class Crawler implements ICrawl
{
	public $Website;
	public $Keywords;
	public $crawledLinks;
	public $ResponseObject;

	function Crawler($Websites, $Keywords)
	{
		try
		{
			$this->Website=$Websites;
			$this->Keywords=$Keywords;
			$this->crawledLinks = array();
			//print_r($this->Websites);
			//print_r($this->Keywords);
		}
		catch(Exception $ex)
		{
			//Log exception to file/db
		}		
	}

	public function Crawl() 
	{
		try
		{
			$this->GetChildURL($this->Website);

			#Removing Duplicate Links
			$this->crawledLinks = array_unique($this->crawledLinks);

			#Removing Social Links and Creating a Link Object to Process
			$LinkObject['link']= array();
			$LinkObject['dom']= array();
			$c=0;
			for ($i=0; $i < count($this->crawledLinks); $i++) 
			{ 
	    		$domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $this->Website);
	    		$link = $this->crawledLinks[$i];
	    		//echo $domain.'<br><br>';
	    		$pos =strpos($link, $domain);
	    		if($pos !== false)
	    		{
	    			#Create Link Object
	    			$LinkObject['link'][$c] = $link;
			        $dom = new DOMDocument('1.0');
		    		@$dom->loadHTMLFile($link);
			        $LinkObject['dom'][$c] = strtolower($dom->textContent);
			        $c++;
	    		}
			}

			#Get Search Response
			$Response = $this->SearchDOMBody($LinkObject);
			return json_encode($Response);	
		}
		catch(Exception $ex)
		{
			//Log exception to file/db
		}	
	}

	public function GetChildURL($url)
	{
		try
		{
			$this->followLink($url);
		}
		catch(Exception $ex)
		{

		}		
	}
	
	public function SearchDOMBody($LinkObject)
	{		
		$ResponseObject["domain"] = array();
		$ResponseObject["domain"]["name"]= $this->Website;
		$key = $this->Keywords;
		for ($i=0; $i < count($LinkObject['link']); $i++) 
		{ 
			$ResponseObject["domain"]["url"][$i]["original_link"] = $LinkObject['link'][$i];
			$j=0;
			$MessageObject["matches"] = array();
			foreach($key as &$k)
		    {
		    	$Result = substr_count($LinkObject['dom'][$i],strtolower(ltrim(rtrim($k))));
		    	$MessageObject["matches"][$j]["keyword"] = $k;
				$MessageObject["matches"][$j]["no_of_matches"] = $Result; 
				$MessageObject["matches"][$j]["is_match_found"] = ($Result>0 ? true : false);
				$MessageObject["matches"][$j]["matching_strings"] = ($Result>0 ? $k : "");
		        $j++;
		    }
			$ResponseObject["domain"]["url"][$i]["matches"] = $MessageObject["matches"];
			$ResponseObject["domain"]["url"][$i]["status"] = 200;
			$ResponseObject["domain"]["url"][$i]["error"]["code"] = array();
			$ResponseObject["domain"]["url"][$i]["error"]["message"] = array();			
		}
		return $ResponseObject;
		//var_dump(json_encode($ResponseObject));	
	}

	public function followLink($url, $depth = 2)
	{ 
        $crawling = array(); 
  
        // Give up to prevent any seemingly infinite loop 
        if ($depth>2)
        { 
        	return; 
        } 
          
        $options = array( 
            'http' => array( 
                'method' => "GET", 
                'user-agent' => "skBot/0.1\n"
            ) 
        ); 
  
        $context = stream_context_create($options); 
        $doc = new DomDocument(); 
        @$doc -> loadHTML(file_get_contents($url, false, $context)); 
        $links = $doc->getElementsByTagName('a'); 
  
        foreach ($links as $i)
        { 
            $link = $i->getAttribute('href'); 
            if ($this->ignoreLink($link)) continue; 
  
            $link = $this->convertLink($url, $link); 
              
            if (!in_array($link, $this->crawledLinks))
            { 
                $this->crawledLinks[] = $link; 
                $crawling[] = $link; 
                $this->insertIntoDatabase($link, $depth); 
            } 
        } 

        foreach ($crawling as $crawlURL)
        { 
            $this->followLink($crawlURL, $depth+1); 
        }
    } 
  
    // Converts Relative URL to Absolute URL 
    // No conversion is done if it is already in Absolute URL 
    public function convertLink($site, $path)
    {
    	try
		{
			if (substr_compare($path, "//", 0, 2) == 0) 
            return parse_url($site)['scheme'].$path; 
	        elseif (substr_compare($path, "http://", 0, 7) == 0 or
	            substr_compare($path, "https://", 0, 8) == 0 or 
	            substr_compare($path, "www.", 0, 4) == 0) 
	  
	            return $path; // Absolutely an Absolute URL!! 
	        else
	            return $site.'/'.$path; 
		}
		catch(Exception $ex)
		{
			//Log exception to file/db
		}        
    } 
  
    // Whether or not we want to ignore the link 
    public function ignoreLink($url)
    { 
    	try
		{
			return $url[0]=="#" or substr($url, 0, 11) == "javascript:"; 
		}
		catch(Exception $ex)
		{
			//Log exception to file/db
		}        
    } 
  
    // Print a message and insert into the array/database! 
    public function insertIntoDatabase($link, $depth)
    {
    	try
		{
			$this->crawledLinks[]=$link; 
		}
		catch(Exception $ex)
		{
			//Log exception to file/db
		}        
    } 
}
?>