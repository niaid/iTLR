<?php

namespace iTLR\Upload\Materials;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\SheetIterator;
use iTLR\Helpers\Prints;

class File
{
	public static function handleUpload($fileName, $index = NULL)
    {
        $result = array('Valid' => FALSE, 'Error' => '');

        $isMultiple = (!is_null($index)) ? TRUE : FALSE;

        if (($isMultiple && isset($_FILES[$fileName]['name'][$index])) || ((!$isMultiple) && $_FILES[$fileName]['name'])) {
            //if the file exists
            $file = $_FILES[$fileName];
            $result['Valid'] = TRUE;


            if (($isMultiple && $file["error"][$index] > 0) || (!$isMultiple && $file['error'] > 0)) {
                $result['Valid'] = FALSE;
                //echo $fileName.'.'.$index.'.'.$isMultiple;
                $file_name = ($isMultiple) ? $file['name'][$index] : $file['error'];
                $file_name = htmlspecialchars($file_name);
                $result['Error'] = 'PHP Upload File Error (' . $file_name . '):';
                $result['Error'] .= ($isMultiple) ? $file['error'][$index] : $file['error'];

                return $result;
            }

            $result['File']['name'] = ($isMultiple) ? $file['name'][$index] : $file['name'];
            $result['File']['type'] = ($isMultiple) ? $file['type'][$index] : $file['type'];
            $result['File']['tmp_name'] = ($isMultiple) ? $file['tmp_name'][$index] : $file['tmp_name'];
            $result['File']['size'] = ($isMultiple) ? $file['size'][$index] : $file['size'];

            if (($isMultiple && file_exists($file['tmp_name'][$index])) || (!$isMultiple && file_exists($file['tmp_name']))) {
                $filePath = ($isMultiple) ? $file['tmp_name'][$index] : $file['tmp_name'];

                $data = NULL;

                print_r($result);

                if($result['File']['type'] == 'text/csv')
                {
                    $data = self::readCSV($filePath);
                }
                else if(strpos($result['File']['name'], 'xlsx') !== FALSE)
                {
                    $data = self::readXLSX($filePath);
                }

                if($data == NULL)
                {
                    $result['Valid'] = FALSE;
                    $result['Error'] = 'Unknown File Format';
                }

                $result['File']['data'] = $data;

            } else {
                $result['Valid'] = FALSE;
                $result['Error'] = 'File does not exist';
            }
        } else {
            $result['Valid'] = FALSE;
            $result['Error'] = 'Invalid File: Cannot give you more information than that';
        }

        return $result;
    }


    private static function readCSV($filePath)
    {
        $reader = ReaderFactory::create(Type::CSV);
        $reader->setFieldDelimiter(',');

        return self::read($reader, $filePath);
    }

    private static function readXLSX($filePath)
    {
        $reader = ReaderFactory::create(Type::XLSX);

        return self::read($reader, $filePath);
    }

    private static function read(ReaderInterface $reader, $filePath)
    {

        $reader->open($filePath);

        $header = array();
        $data = array();

        //Only reading the first sheet
        $i = 0;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if($i == 0)
                {
                    $header = $row;
                    $data[] = $header;
                    $i++;
                    continue;
                }

                $combined = array();
                foreach (array_keys($header) as $index => $key) {
                    $combined[$index] = isset($row[$index]) ? $row[$index] : null;
                }

                $data[] = $combined;
            }

            break;
        }

        $reader->close();

        return $data;
    }


}
