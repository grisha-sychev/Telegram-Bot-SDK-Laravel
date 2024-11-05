<?php

namespace Teg\Types;

class Poll implements \Teg\Types\Interface\InitObject
{
    private $id;
    private $question;
    private $question_entities;
    private $options;
    private $total_voter_count;
    private $is_closed;
    private $is_anonymous;
    private $type;
    private $allows_multiple_answers;
    private $correct_option_id;
    private $explanation;
    private $explanation_entities;
    private $open_period;
    private $close_date;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->id = $request->id ?? null;
        $this->question = $request->question ?? null;
        $this->question_entities = new MessageEntity($request->question_entities) ?? [];
        $this->options = new PollOption($request->options) ?? [];
        $this->total_voter_count = $request->total_voter_count ?? 0;
        $this->is_closed = $request->is_closed ?? false;
        $this->is_anonymous = $request->is_anonymous ?? false;
        $this->type = $request->type ?? '';
        $this->allows_multiple_answers = $request->allows_multiple_answers ?? false;
        $this->correct_option_id = $request->correct_option_id ?? null;
        $this->explanation = $request->explanation ?? '';
        $this->explanation_entities = new MessageEntity($request->explanation_entities) ?? [];
        $this->open_period = $request->open_period ?? null;
        $this->close_date = $request->close_date ?? null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getQuestionEntities()
    {
        return $this->question_entities;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getTotalVoterCount()
    {
        return $this->total_voter_count;
    }

    public function getIsClosed()
    {
        return $this->is_closed;
    }

    public function getIsAnonymous()
    {
        return $this->is_anonymous;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAllowsMultipleAnswers()
    {
        return $this->allows_multiple_answers;
    }

    public function getCorrectOptionId()
    {
        return $this->correct_option_id;
    }

    public function getExplanation()
    {
        return $this->explanation;
    }

    public function getExplanationEntities()
    {
        return $this->explanation_entities;
    }

    public function getOpenPeriod()
    {
        return $this->open_period;
    }

    public function getCloseDate()
    {
        return $this->close_date;
    }
}
