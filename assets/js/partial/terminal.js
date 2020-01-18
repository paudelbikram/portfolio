import $ from "jquery";
import {Terminal} from "xterm";
import {FitAddon} from "xterm-addon-fit";
import {commandExit, commandLoad, commandOther} from "./terminal-commands";

const promptText = 'visitor@hoffic.dev:~$ ';

export function setUpTerminal(terminalElement) {
  const terminal = new Terminal({
    theme: {
      background: '#333333', // in colours.scss as $color-terminal-content
    }
  });
  const fitAddon = new FitAddon();

  terminal.loadAddon(fitAddon);
  terminal.open(terminalElement);
  fitAddon.fit();

  terminal.widget = $(terminalElement).closest('.terminal-widget');

  configureInput(terminal);
  configureButtons(terminal);

  terminal.prompt();

  commandLoad();
}

function configureInput(terminal) {
  terminal.inputBuffer = '';

  terminal.onKey(function (event) {
    switch (event.key.charCodeAt(0)) {
      case 13:
        keyEnter(terminal, event.key);
        break;
      case 127:
        keyBackspace(terminal, event.key);
        break;
      default:
        keyOther(terminal, event.key);
        break;
    }
  });

  terminal.prompt = function () {
    terminal.write(promptText);
  }
}

function keyEnter(terminal, key) {
  terminal.write('\n');
  terminal.write(key);

  let input = terminal.inputBuffer;
  terminal.inputBuffer = '';
  command(terminal, input, function () {
    terminal.prompt();
  });
}

function keyBackspace(terminal) {
  if (terminal._core.buffer.x > promptText.length) {
    terminal.write('\b \b');
    terminal.inputBuffer = terminal.inputBuffer.substring(
        0,
        terminal.inputBuffer.length - 1);
  }
}

function keyOther(terminal, key) {
  // Excluding common non-printable characters
  if (key.charCodeAt(0) >= 32 && key.charCodeAt(0) !== 127) {
    terminal.write(key);
    terminal.inputBuffer += key;
  }
}

function command(terminal, command, commandCallback) {
  switch (command) {
    case '':
      commandCallback();
      break;
    case 'exit':
      commandExit(terminal);
      break;
    default:
      commandOther(terminal, command, commandCallback);
      break;
  }
}

function configureButtons(terminal) {
  terminal.widget.find('.close-button img').first().click(function () {
    commandExit(terminal);
  });
}