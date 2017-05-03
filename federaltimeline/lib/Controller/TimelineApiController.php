<?php
namespace OCA\FederalTimeline\Controller;

use \DateTime;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\ApiController;

class TimelineApiController extends ApiController {
	private $userId;
	private $userFolder;
	private $systemTagManager;
	private $systemTagObjectMapper;

	public function __construct($AppName, IRequest $request, $UserId, $ServerContainer){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->userFolder = $ServerContainer->getUserFolder();
		$this->systemTagManager = $ServerContainer->getSystemTagManager();
		$this->systemTagObjectMapper = $ServerContainer->getSystemTagObjectMapper();
	}

	/**
	 * @NoAdminRequired
	 */
	public function index() {
		$tlTag = $this->systemTagManager->getTag('timeline', true, true);
		if(!$tlTag){
			return new JSONResponse([], \OCP\AppFramework\Http::STATUS_NO_CONTENT);
		}
		$tlobjects = $this->systemTagObjectMapper->getObjectIdsForTags($tlTag->getId(), 'files');
		$tagIds = $this->systemTagObjectMapper->getTagIdsForObjects($tlobjects, 'files');
		$dateInstances = [];
		$untaggedFiles = [];
		$uploadFolder = '';
		foreach ($tlobjects as $tlo) {
			$di = [];
			foreach ($this->systemTagManager->getTagsByIds($tagIds[$tlo]) as $tag) {
				$diData = [];
				if(preg_match('/^tl:(.*)/', $tag->getName(), $diData)){
					if(preg_match('#^../../....$#', $diData[1])){
						$dpff = date_create_from_format('!d/m/Y', $diData[1]);
						$di['di_date'] = date_timestamp_get($dpff);
					} else {
						$di['di_instance'] = $diData[1];
					}
				}
				$di['tags'][] = $tag->getName();
			}
			$files = $this->userFolder->getById($tlo);
			if(count($files)){
				foreach ($files as $file) {
					$di['id'] = $file->getId();
					$di['name'] = $file->getName();
					$di['mimetype'] = $file->getMimetype();
					$uploadFolder = $file->getParent()->getInternalPath();
				}
				if($di['di_date'] && $di['di_instance']){
					$dateInstances[] = $di;
				} else {
					$untaggedFiles[] = $di;
				}
			}
		}
		$uploadFolder = explode('/', $uploadFolder);
		array_shift($uploadFolder);
		$uploadFolder = implode('/', $uploadFolder);
		return [
			'dateInstances' => $dateInstances,
			'untaggedFiles' => $untaggedFiles,
			'uploadFolder' => $uploadFolder,
		];
	}

	/**
	 * @NoAdminRequired
	 * @param $date
	 * @param $instance
	 * @param $uploadFolder
	 */
	public function uploadFile($date, $instance, $uploadFolder)
	{
		$upFile = $this->request->getUploadedFile('file');
		if(!is_array($upFile['name'])){
			$upFile['name'] = [$upFile['name']];
			$upFile['type'] = [$upFile['type']];
			$upFile['tmp_name'] = [$upFile['tmp_name']];
			$upFile['size'] = [$upFile['size']];
			$upFile['error'] = [$upFile['error']];
		}
		$date = date('d/m/Y', $date);
		// Find or create $tagIds
		$tagIds = [];
		try{
			$tagIds['timeline'] = $this->systemTagManager->getTag('timeline', true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['timeline'] = $this->systemTagManager->createTag('timeline', true, true)->getId();
		}
		try{
			$tagIds['tl:date'] = $this->systemTagManager->getTag('tl:'.$date, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:date'] = $this->systemTagManager->createTag('tl:'.$date, true, true)->getId();
		}
		try{
			$tagIds['tl:instance'] = $this->systemTagManager->getTag('tl:'.$instance, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:instance'] = $this->systemTagManager->createTag('tl:'.$instance, true, true)->getId();
		}
		// Grab the folder
		$folder = $this->userFolder->get($uploadFolder);
		if(!$folder){
			return new JSONResponse('', \OCP\AppFramework\Http::STATUS_NOT_FOUND);
		} else if($folder->isCreatable() == false) {
			return new JSONResponse('', \OCP\AppFramework\Http::STATUS_FORBIDDEN);
		}
		// Create files
		$ret = [];
		for ($i=0; $i < count($upFile['name']); $i++) {
			$name = $folder->getNonExistingName($upFile['name'][$i]);
			$nfile = $folder->newFile($name);
			$nfile->putContent(file_get_contents($upFile['tmp_name'][$i]));
			
			$this->systemTagObjectMapper->assignTags($nfile->getId(), 'files', $tagIds);

			$ret[] = [
				'id' => $nfile->getId(),
				'name' => $nfile->getName()
			];
		}
		return $ret;
	}

	/**
	 * @NoAdminRequired
	 * @param int $fileId
	 */
	public function downloadFile($fileId)
	{
		$files = $this->userFolder->getById($fileId);
		//TODO check permissions
		if(count($files)){
			return new DataDownloadResponse($files[0]->getContent(), $files[0]->getName(), $files[0]->getMimetype());
		}

		return new JSONResponse("", \OCP\AppFramework\Http::STATUS_NOT_FOUND);
	}

	/**
	 * @NoAdminRequired
	 * @param int $fileId
	 * @param $date
	 * @param $instance
	 */
	public function tagFile($fileId, $date, $instance)
	{
		$files = $this->userFolder->getById($fileId);
		if(count($files) == 0){
			return new JSONResponse("", \OCP\AppFramework\Http::STATUS_NOT_FOUND);
		}

		$date = date('d/m/Y', $date);
		// Find or create $tagIds
		$tagIds = [];
		try{
			$tagIds['timeline'] = $this->systemTagManager->getTag('timeline', true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['timeline'] = $this->systemTagManager->createTag('timeline', true, true)->getId();
		}
		try{
			$tagIds['tl:date'] = $this->systemTagManager->getTag('tl:'.$date, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:date'] = $this->systemTagManager->createTag('tl:'.$date, true, true)->getId();
		}
		try{
			$tagIds['tl:instance'] = $this->systemTagManager->getTag('tl:'.$instance, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:instance'] = $this->systemTagManager->createTag('tl:'.$instance, true, true)->getId();
		}

		$this->systemTagObjectMapper->assignTags($fileId, 'files', $tagIds);

		return [];
	}
}
