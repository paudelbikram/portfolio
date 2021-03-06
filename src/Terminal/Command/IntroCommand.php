<?php
declare(strict_types=1);


namespace App\Terminal\Command;


use App\Object\CommandOutput;
use App\Terminal\History;

class IntroCommand implements Command
{
    /**
     * @param array $params
     * @param History|null $history
     * @return CommandOutput
     */
    public function execute(array $params, ?History $history = null): CommandOutput
    {
        if ($history->hasNote(ExitCommand::HAS_REOPENED)
            && $history->hasNote(ExitCommand::EXIT_TIME_KEY)) {
            $output = $this->improveScore($history);
        } elseif ($history->hasNote(ExitCommand::EXIT_TIME_KEY)) {
            $output = $this->reopeningTerminal($history);
        } elseif ($history->hasNote(VimCommand::START_TIME_KEY)) {
            $output = $this->gaveUpMaze($history);
        } else {
            $output = $this->classicIntro();
        }

        return $output;
    }

    /**
     * @return CommandOutput
     */
    private function classicIntro(): CommandOutput
    {
        return new CommandOutput(/** @lang text */ <<<STDOUT


        I'm a software engineer based in London/Prague specializing in building

        reliable back ends, helping out with smaller scale DevOps and training

        a neural network for best engineering practices.


        You might want to click here and type in 'ls' or 'help' to get started.


STDOUT
        );
    }

    /**
     * @param History $history
     * @return CommandOutput
     */
    private function reopeningTerminal(History $history): CommandOutput
    {
        $amount = $this->gatherTimeAmount($history);

        $output = $this->classicIntro();
        $output->setAlert(sprintf(
            'Well done! It took you %.2f seconds to reopen the terminal!',
            $amount));

        return $output;
    }

    private function improveScore(History $history): CommandOutput
    {
        $amount = $this->gatherTimeAmount($history);

        $output = $this->classicIntro();

        $output->setAlert(sprintf(
            "Trying to improve your score? That's not fair :P Btw it was %.2f seconds...",
            $amount));

        return $output;
    }

    private function gatherTimeAmount(History $history): float
    {
        $amount = microtime(true) - $history->getNote(ExitCommand::EXIT_TIME_KEY);
        $history->unsetNote(ExitCommand::EXIT_TIME_KEY);
        $history->setNote(ExitCommand::HAS_REOPENED, true);

        return $amount;
    }

    private function gaveUpMaze(?History $history): CommandOutput
    {
        $output = new CommandOutput();

        $output->setAlert('Next time try :q<enter> to exit vim properly :P');

        $history->unsetNote(VimCommand::START_TIME_KEY);

        return $output;
    }
}
