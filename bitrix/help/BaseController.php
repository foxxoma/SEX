<?php
namespace Odva\Module;

use \Bitrix\Main\Engine\Controller;

class BaseController extends Controller
{
	public function getJRequest()
	{
		$request = $this->getRequest()->getInput();
		$request = json_decode($request, true);

		if(empty($request))
			$request = $this->getRequest()->getPostList()->getValues();

		return $request;
	}

	public function getFiles()
	{
		$allFiles = $this->getRequest()->getFileList()->getValues();

		if(empty($allFiles))
			return [];

		$result = [];

		foreach($allFiles as $name => $files)
		{
			$arFiles = [];

			if (empty($files['size']))
				continue;

			if(!is_array($files['size']))
			{
				$result[$name] = $files;
			}
			else
			{
				if (empty($files['size'][array_key_first($files['size'])]))
					continue;

				foreach ($files['size'] as $key => $size)
				{
					$arFiles[] = [
						'name' => $files['name'][$key],
						'type' => $files['type'][$key],
						'tmp_name' => $files['tmp_name'][$key],
						'error' => $files['error'][$key],
						'size' => $files['size'][$key],
					];
				}

				$result[$name] = $arFiles;
			}
		}

		return $result;
	}
}
