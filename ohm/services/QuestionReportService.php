<?php

namespace OHM\Services;

use PDO;

// Collect data on a set of questions
class QuestionReportService
{
    private $startDate = '';

    private $endDate = '';

    private $startModDate = '';

    private $endModDate = '';

    private $noAssessment = '';

    private $dbh;

    private $questions = [];

    private $userRightsDistribution = [
        '0' => 0, // Private
        '1' => 0, // Outdated, should've been replaced by 4
        '2' => 0, // Allow Use By All
        '3' => 0, // Allow use by all and modifications by group
        '4' => 0, // Allow use by all and modifications by all
        'Unspecified' => 0
    ];

    private $uniqueUserIds = [];

    private $uniqueGroupIds = [];

    private $users = [];

    private $groups = [];

    public function __construct(
        $dbh,
        $startDate,
        $endDate,
        $startModDate,
        $endModDate,
        $noAssessment
    )
    {
        $this->dbh = $dbh;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->startModDate = $startModDate;
        $this->endModDate = $endModDate;
        $this->noAssessment = $noAssessment;
    }

    public function generateReport()
    {
        $questions = $this->queryQuestions();
        $this->aggregateQuestionData();
        $users = $this->queryUsers();
        $groups = $this->queryGroups();

        return [
          'questions' => $questions,
          'users' => $users,
          'groups' => $groups,
          'userRightsDistribution' => $this->userRightsDistribution,
        ];
    }

    public function queryQuestions()
    {
        $query = "SELECT qs.id, qs.userights, qs.ownerid, qs.adddate, qs.lastmoddate, u.groupid 
              FROM imas_questionset AS qs 
              JOIN imas_users AS u ON qs.ownerid = u.id
              WHERE qs.deleted=0";

        $params = [];

        if (!empty($this->startDate)) {
            $query .= " AND qs.adddate >= :start_date";
            $params[':start_date'] = strtotime($this->startDate);
        }

        if (!empty($this->endDate)) {
            $query .= " AND qs.adddate <= :end_date";
            $params[':end_date'] = strtotime($this->endDate . ' 23:59:59');
        }

        if (!empty($this->startModDate)) {
            $query .= " AND qs.lastmoddate >= :start_mod_date";
            $params[':start_mod_date'] = strtotime($this->startModDate);
        }

        if (!empty($this->endModDate)) {
            $query .= " AND qs.lastmoddate <= :end_mod_date";
            $params[':end_mod_date'] = strtotime($this->endModDate . ' 23:59:59');
        }

        if ($this->noAssessment) {
            $query .= " AND qs.id NOT IN (SELECT DISTINCT questionsetid FROM imas_questions)";
        }

        // Execute the query
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        $this->questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->questions;
    }

    public function aggregateQuestionData()
    {
        foreach ($this->questions as $question) {
            if (isset($this->userRightsDistribution[$question['userights']])) {
                $this->userRightsDistribution[$question['userights']]++;
            } else {
                $this->userRightsDistribution['Unspecified']++;
            }

            // Track unique users
            if (!in_array($question['ownerid'], $this->uniqueUserIds)) {
                $this->uniqueUserIds[] = $question['ownerid'];
            }

            // Track unique groups
            if (!empty($question['groupid']) && !in_array($question['groupid'], $this->uniqueGroupIds)) {
                $this->uniqueGroupIds[] = $question['groupid'];
            }
        }
    }

    public function queryUsers()
    {
        // Get user details
        if (!empty($this->uniqueUserIds)) {
            $placeholders = str_repeat('?,', count($this->uniqueUserIds) - 1) . '?';
            $query = "SELECT u.id, u.FirstName, u.LastName, u.rights, u.groupid, g.name AS groupname 
                  FROM imas_users AS u 
                  LEFT JOIN imas_groups AS g ON u.groupid = g.id 
                  WHERE u.id IN ($placeholders)";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($this->uniqueUserIds);
            $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->users;
    }

    public function queryGroups()
    {
        if (!empty($this->uniqueGroupIds)) {
            $placeholders = str_repeat('?,', count($this->uniqueGroupIds) - 1) . '?';
            $query = "SELECT id, name, grouptype 
                  FROM imas_groups 
                  WHERE id IN ($placeholders)";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($this->uniqueGroupIds);
            $this->groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->groups;
    }


    public function getQuestions()
    {
        return $this->questions;
    }

    public function getUserRightsDistribution()
    {
        return $this->userRightsDistribution;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}