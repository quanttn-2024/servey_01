<?php

namespace App\Traits;

trait ManageSurvey
{
    public function close($survey)
    {
        $idSections = $survey->sections()->pluck('id')->all();
        $idQuestions = $this->questionRepository->whereIn('section_id', $idSections)->get()->pluck('id')->all();
        $this->resultRepository->closeFromSurvey($survey);
        $this->answerRepository->closeFromQuestionId($idQuestions);
        $this->questionRepository->closeFromSectionId($idSections);
        $this->sectionRepository->closeFromSurvey($survey);
        $this->surveyRepository->updateSurvey($survey, ['status' => config('settings.survey.status.close')]);
        $this->surveyRepository->closeSurvey($survey);
    }

    public function open($survey)
    {
        $idSections = $survey->sections()->onlyTrashed()->pluck('id')->all();
        $idQuestions = $this->questionRepository->onlyTrashed()->whereIn('section_id', $idSections)->get()->pluck('id')->all();
        $this->resultRepository->openFromSurvey($survey);
        $this->answerRepository->openFromQuestionId($idQuestions);
        $this->questionRepository->openFromSectionId($idSections);
        $this->sectionRepository->openFromSurvey($survey);
        $this->surveyRepository->updateSurvey($survey, ['status' => config('settings.survey.status.open')]);
        $this->surveyRepository->openSurvey($survey);
    }

    public function delete($survey)
    {
        $idSections = $survey->sections()->withTrashed()->pluck('id')->all();
        $idQuestions = $this->questionRepository->withTrashed()->whereIn('section_id', $idSections)->get()->pluck('id')->all();
        $this->resultRepository->deleteFromSurvey($survey);
        $this->answerRepository->deleteFromQuestionId($idQuestions);
        $this->questionRepository->deleteFromSectionId($idSections);
        $this->sectionRepository->deleteFromSurvey($survey);
        $this->surveyRepository->deleteSurvey($survey);
    }
}