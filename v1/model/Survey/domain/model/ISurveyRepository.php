<?php

namespace model\Survey\domain\model;

use model\common\Entity;
use model\common\IPersistenceProvider;

interface ISurveyRepository extends IPersistenceProvider
{
    public function find(SurveyId $id) : ?Survey;

    public function save(Entity $entity);

    public function remove(string $id);

    public function nextId():SurveyId;

}
