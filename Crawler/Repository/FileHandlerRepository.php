<?php
interface IReadTxtFile
{
	public function ReadFile($FileName);
}

interface IWriteJsonFile
{
	public function WriteFile($ResponseObject);
}

class FileHandler implements IReadTxtFile, IWriteJsonFile
{
	public function ReadFile($filename)
	{
		try
		{
			$response = array();
			$myfile = fopen($filename, "r") or die("Unable to open file!");
			$i=0;
			while(!feof($myfile)) 
			{
			  $response['name'][$i]=stripcslashes(fgets($myfile));
			  $i++;
			}
			fclose($myfile);
			
			if($i > 0)
			{
				$response["status_code"] = 200;
				$response["status"] = "OK";
			}
			return $response;
		}
		catch(Exception $ex)
		{
			//Log exception to DB/file
		}	
	}

	public function WriteFile($ResponseObject)
	{
		try
		{
			$fp = fopen('CrawlerResult/result.json', 'w');
			fwrite($fp, $ResponseObject);
			fclose($fp);
		}
		catch(Exception $ex)
		{
			//Log exception to DB/file
		}		
	}
}

?>