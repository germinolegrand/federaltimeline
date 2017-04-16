<?php
namespace OCA\FederalTimeline\Controller;

use \DateTime;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
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
	 * 
	 */
	public function index() {
		$tlTag = $this->systemTagManager->getTag('timeline', true, true);
		if(!$tlTag){
			return new JSONResponse([], \OCP\AppFramework\Http::STATUS_NO_CONTENT);
		}
		$tlobjects = $this->systemTagObjectMapper->getObjectIdsForTags($tlTag->getId(), 'files');
		$tagIds = $this->systemTagObjectMapper->getTagIdsForObjects($tlobjects, 'files');
		$dateinstances = [];
		foreach ($tlobjects as $tlo) {
			$di = [];
			$di['$tagIds'] = $tagIds;
			$di['count($tagIds)'] = count($tagIds);
			$di['$tagIds[$tlo]'] = $tagIds[$tlo];
			$di['count($tagIds[$tlo])'] = count($tagIds[$tlo]);
			$di['$this->systemTagManager->getTagsByIds($tagIds[$tlo])'] = $this->systemTagManager->getTagsByIds($tagIds[$tlo]);
			$di['count($this->systemTagManager->getTagsByIds($tagIds[$tlo]))'] = count($this->systemTagManager->getTagsByIds($tagIds[$tlo]));
			foreach ($this->systemTagManager->getTagsByIds($tagIds[$tlo]) as $tag) {
				$diData = [];
				if(preg_match('/^tl:(.*)/', $tag->getName(), $diData)){
					if(preg_match('#^../../....$#', $diData[1])){
						$dpff = date_create_from_format('!d/m/Y', $diData[1]);
						$di['di_date'] = date_timestamp_get($dpff)*1000;
					} else {
						$di['di_instance'] = $diData[1];
					}
				}
				$di['tags'][] = $tag->getName();
			}
			foreach ($this->userFolder->getById($tlo) as $value) {
				$di['id'] = $value->getId();
				$di['name'] = $value->getName();
				$di['mimetype'] = $value->getMimetype();
			}
			$dateinstances[] = $di;
		}
		return new JSONResponse($dateinstances, count($dateinstances) > 0 ? \OCP\AppFramework\Http::STATUS_OK : \OCP\AppFramework\Http::STATUS_NO_CONTENT);
	}

}
