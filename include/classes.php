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
   * 
   * @param text $fileContent
   * Неформатированный текст на входе
   * @return array
   * Массив с контентом построчно
   */
  public function stripping($fileContent) {
    $this->fileContent = $fileContent;
    /**
     * В этой части файл разбивается на строки и обрабатывается построчно
     */
    $this->arrContent = explode("\n", $this->fileContent);  // Запихиваем страницу по строкам в массив
    return $this->arrContent;
  }

  /**
   * 
   * @param array $contentFromArray 
   * Массив с контентом построчно
   * @return text
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
