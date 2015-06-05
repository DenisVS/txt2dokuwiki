<?php

/*
 * @file 
 * Классы
 */

class LineByLine {

  public $fileContent;
  public $arrContent;
  public $contentFromArray;

  /**
   * Преобразование строки в массив
   * @param string $fileContent
   * Неформатированный текст на входе
   * @return array
   * Массив с контентом построчно
   */
  public function stripping($fileContent) {
    $this->fileContent = $fileContent;
    $this->arrContent = explode("\n", $this->fileContent);  // Запихиваем страницу по строкам в массив
    return $this->arrContent;
  }

  /**
   * Преобразование массива в строку
   * @param array $contentFromArray 
   * Массив с контентом построчно
   * @return string
   * Неформатированный текст на выходе
   */
  public function assembling($contentFromArray) {
    $this->contentFromArray = $contentFromArray;
    $this->txtContent = FALSE;
    foreach ($this->contentFromArray as $key => $val) {
      $this->txtContent .= $val . "\n";
    }
    $this->txtContent = substr($this->txtContent, 0, -1);  ///< Отрубаем последний перенос
    return $this->txtContent;
  }

}

/**
 * Класс контроля над первым или последним символом.
 * Символ может быть любым, он может или быть, или не быть
 * в обязательном порядке.
 */
class ControlEdgeSymbol {
  /*   * @var string $text Строка на входе */

  public $text;
  /*   * @var string $symbol Искомый символ */
  public $symbol;
  /*   * @var boolean $symbolSholdBe Должен ли быть символ */
  public $symbolSholdBe;
  /*   * @var string $position Контролируемое расположение (START, END) */
  public $position;

  /**
   * Проверка наличия символа с заданного края
   * @param string $text Строка на входе
   * @param string $symbol Искомый символ
   * @param string $position Проверяемое расположение (START, END)
   * @return boolean 
   */
  function checkingForSymbol() {
    if ($this->position == 'END') {
      $pos = mb_strpos($this->text, $this->symbol, mb_strlen($this->text) - 1);
      if ($pos === false) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    }
    else if ($this->position == 'START') {
      $pos = mb_strpos($this->text, $this->symbol);
      if ($pos == 0) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return 'Unknown position: ' . $this->position;
    }
  }

  /**
   * Контроль символа с заданного края
   * @param string $text Строка на входе
   * @param string $symbol Контролируемый символ
   * @param boolean $symbolSholdBe Должен ли быть символ
   * @param string $position Контролируемое расположение (START, END)
   * @return array Данные на выходе: строка и длина.
   */
  public function controlStartEndSymbol() {
    if ($this->symbolSholdBe == TRUE) {
      if (checkingForSymbol($this->text, $this->symbol, $this->position) == TRUE) {
        $this->out['text'] = $this->text;
        $this->out['lenght'] = mb_strlen($this->text);
        return $this->out;
      }
      else {
        switch ($this->position) {
          case 'END':
            $this->out['text'] = $this->text . $this->symbol;
            break;
          case 'START':
            $this->out['text'] = $this->symbol . $this->text;
            break;
          default:
            $this->out['text'] = 'ERROR';
        }
        $this->out['lenght'] = mb_strlen($this->out['text']);
        return $this->out;
      }
    }
    else {
      if ($this->checkingForSymbol($this->text, $this->symbol, $this->position) == TRUE) {
        switch ($this->position) {
          case 'END':
            $this->text = mb_substr($this->text, 0, mb_strlen($this->text) - 1);  // Рубим последний символ
            $this->out['text'] = $this->text;
            break;
          case ('START'):
            $this->text = mb_substr($this->text, 1);  // Рубим первый символ
            $this->out['text'] = $this->text;
            break;
        }
        $this->out['lenght'] = mb_strlen($this->text);
        return $this->out;
      }
      else {
        $this->out['text'] = $this->text;
        $this->out['lenght'] = mb_strlen($this->text);
        return $this->out;
      }
    }
  }

}
