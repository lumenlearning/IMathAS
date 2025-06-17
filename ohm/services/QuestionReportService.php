<?php

namespace OHM\Services;

use PDO;

// Collect data on a set of questions
class QuestionReportService
{
    private $startDate;

    private $endDate;

    private $startModDate;

    private $endModDate;

    private $noAssessment;

    private $minId;

    private $maxId;

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
        $noAssessment,
        $minId,
        $maxId
    )
    {
        $this->dbh = $dbh;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->startModDate = $startModDate;
        $this->endModDate = $endModDate;
        $this->noAssessment = $noAssessment;
        $this->minId = $minId;
        $this->maxId = $maxId;
    }

    public function generateReport(): array
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

    public function queryQuestions(): array
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
            $params[':end_date'] = strtotime($this->endDate . ' 23:59:59'); // inclusive of endDate
        }

        if (!empty($this->startModDate)) {
            $query .= " AND qs.lastmoddate >= :start_mod_date";
            $params[':start_mod_date'] = strtotime($this->startModDate);
        }

        if (!empty($this->endModDate)) {
            $query .= " AND qs.lastmoddate <= :end_mod_date";
            $params[':end_mod_date'] = strtotime($this->endModDate . ' 23:59:59'); // inclusive of endModDate
        }

        if ($this->noAssessment) {
            $query .= " AND qs.id NOT IN (SELECT DISTINCT questionsetid FROM imas_questions)";
        }

        if (isset($this->minId)) {
            $query .= " AND qs.id >= :min_id";
            $params[':min_id'] = $this->minId;
        }

        if (isset($this->maxId)) {
            $query .= " AND qs.id <= :max_id";
            $params[':max_id'] = $this->maxId;
        }

        // Execute the query
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        $this->questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->questions;
    }

    public function aggregateQuestionData(): void
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

    public function queryUsers(): array
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

    public function queryGroups(): array
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


    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getUserRightsDistribution(): array
    {
        return $this->userRightsDistribution;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function questionsToCSVArrays(): array {
        $arrays = array(
            // Column Headers
            array('Question ID', 'User Rights', 'Owner ID', 'Creation Date', 'Last Modified Date', 'Group ID')
        );

        // Add data rows
        foreach ($this->questions as $question) {
            $row = array(
                $question['id'],
                $question['userights'],
                $question['ownerid'],
                date('Y-m-d H:i:s', $question['adddate']),
                date('Y-m-d H:i:s', $question['lastmoddate']),
                $question['groupid']
            );
            $arrays[] = $row;
        }

        return $arrays;
    }

    public function usersToCSVArrays(): array {
        $arrays = array(
            // Column Headers
            array('ID', 'Name', 'Rights', 'Group Name')
        );

        // Add data rows
        foreach ($this->users as $user) {
            $row = array(
                $user['id'],
                $user['FirstName'] . ' ' . $user['LastName'],
                $user['rights'],
                $user['groupname']
            );
            $arrays[] = $row;
        }

        return $arrays;
    }

    public function groupsToCSVArrays(): array {
        $arrays = array(
            // Column Headers
            array('ID', 'Name', 'Group Type')
        );

        // Add data rows
        foreach ($this->groups as $group) {
            $row = array(
                $group['id'],
                $group['name'],
                $group['grouptype'],
            );
            $arrays[] = $row;
        }

        return $arrays;
    }
}