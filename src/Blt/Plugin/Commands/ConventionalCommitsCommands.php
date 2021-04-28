<?php

namespace ConventionalCommit\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Question\Question;
use Example\Blt\Plugin\Tasks\ConventionalCommitsGitTask;

/**
 * Defines commands in the "custom" namespace.
 */
class ConventionalCommitsCommands extends BltTasks {
    /**
     * Creates a Conventional Commit.
     *
     * @command commit
     *
     * @aliases commit
     *
     * @launchWebServer
     * @executeInVm
     *
     * @throws \Acquia\Blt\Robo\Exceptions\BltException
     */
    public function commit() {
        $this->say('commit message ');
        $answers = $this->askForAnswers();
        $this->say("<comment>You have entered the following values:</comment>");
        $this->printArrayAsTable($answers);
        $continue = $this->confirm("Continue?", TRUE);
        if (!$continue) {
            return 1;
        }
        $message[]=$answers['ticket_id'].'@'.$answers['commit_type'].'('.$answers['scope'].'): '.$answers['Subject'];
        $message[]=$answers['body'];
        if (array_key_exists('footer', $answers)) {
            $message[] = implode(PHP_EOL, $answers['footer']);
        }
        $commit_message = implode(PHP_EOL.PHP_EOL,$message);
        $result = $this->taskGit()->commit($commit_message);
        return $result;
    }

    /**
     * Returns answers.
     */
    protected function askForAnswers(){
        $answers['ticket_id'] = $this->doAsk(new Question($this->formatQuestion("Enter TICKET ID ")));
        $commit_type_options = $this->getConfigValue('git.commit-type.description');
        $answers['commit_type'] = $this->askChoice('Select Commit type',$commit_type_options);
        $answers['scope'] = $this->doAsk(new Question($this->formatQuestion("Enter Scope")));
        $answers['Subject'] = $this->doAsk(new Question($this->formatQuestion("Enter Commit Message Subject")));
        $answers['body'] = $this->doAsk(new Question($this->formatQuestion("Enter the Description what it contains[Optional]")));

        do{
            $token_name =  $this->doAsk(new Question($this->formatQuestion("Enter Token Name")));
            if(!$token_name){
                break;
            }
            $token_value =  $this->doAsk(new Question($this->formatQuestion("Enter Token Value")));
            $answers['footer'][] = "$token_name: $token_value";
        }while($token_name!= NULL);
        return $answers;
    }

    /**
     * Validates a git commit message.
     *
     * @hook replace-command internal:git-hook:execute:commit-msg
     */
    public function validateCommitMessage($message) {
        $this->say('Validating Conventional commit message syntax...');
        $delimiter = PHP_EOL.PHP_EOL;
        $commit_message = explode($delimiter,$message);
        if(!$this->validateMessageHeader($commit_message[0])) {
            return 1;
        }
        if(array_key_exists(2, $commit_message)) {
            if(!$this->validateMessageFooter($commit_message[2])) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Validates Message Header and returns true false.
     */
    protected function validateMessageHeader($commit_message_header) {
        $jira_id = $this->getConfigValue('git.ticket-id.pattern');
        $commit_msg_type_regex = $this->getConfigValue('git.commit-type.pattern');
        $commit_msg_scope_regex = '.{1,20}';
        $commit_msg_subject_regex = '.{1,100}';
        $commit_msg_regex = "/($jira_id)?@(${commit_msg_type_regex})(\(${commit_msg_scope_regex}\))?: (${commit_msg_subject_regex})/";
        $example = 'CON-1234@feat(global): Enable Dblog.';
        if (!preg_match($commit_msg_regex, $commit_message_header)) {
            $this->logger->error("Invalid commit message Header");
            $this->say("Commit messages header must conform to the regex $commit_msg_regex");
            if (!empty($example)) {
                $this->say("Commit message Header Example: $example");
            }
            return 0;
        }
        return 1;
    }

    /**
     * Validates Message footer and returns true false.
     */
    protected function validateMessageFooter($commit_message_footer) {
        $commit_message_footer = preg_replace('/#(.*?)[\r\n]|#[\r\n]|#/','', $commit_message_footer);
        $this->say($commit_message_footer);
        $tokens = explode(PHP_EOL, $commit_message_footer);
        foreach($tokens as $token) {
            $token = trim($token);
            if($token) {
                if (!$this->validateToken($token)) {
                    return 0;
                }
            }
        }
        return 1;
    }

    /**
     * Validates Token and returns true false.
     */
    protected function validateToken($token) {
        $token_value = '.{1,20}';
        $token_regex = 'BREAKING CHANGE|([a-zA-Z\-]+)';
        $regex = "/($token_regex)?: ($token_value)/";
        $example = 'Token Name: Value';
        if (!preg_match($regex, $token)) {
            $this->say($regex);
            $this->say($token);
            $this->logger->error("Invalid footer token $token");
            $this->say("Footer Token must conform to the regex $regex");
            if (!empty($example)) {
                $this->say("Footer Token Example: $example");
            }
            return 0;
        }
        return 1;
    }
}
