<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Exception;

class csv_file_parser
{
    // create constants that can be pulled when needed during the script
    public const filepath = 'csv/users_file.csv';
    public const chunk_storage = '/chunk/';
    public function __construct()
    {
        try {


            Log::info('Info - ', ['Starting to Parse']);

            /*
             * Creating file paths from root. (trying to make this to on any machine)
             */
            $filepath = self::filepath;
            $chunkpath = self::chunk_storage;
            $local_path = Storage::disk('local');
            $get_full_file_path = Storage::disk('local')->path($filepath);
            $chunk_file_directory = Storage::disk('local')->path($chunkpath);

            /*
            Building out this directory just in case we want to delete the folder after generating
            */
            if (!is_dir($chunk_file_directory)) {
                Log::info('Info - ', ['Creating Chunk Directory']);
                if (!mkdir($chunk_file_directory, 0700, true) && !is_dir($chunk_file_directory)) {
                    Log::info('Info - ', ['Could not create directory' . $chunk_file_directory]);
                }
            }

            /*
             * Lets take a file and break it into small chunks so that we can create bite sized files to parse
             * Typically if you have a CSV with a million rows or so you might want to store that in the database.
             * Creating bite sizes files from one large file will allow you to do this quickly
             */
            $splitSize = 10; // 10
            $in = fopen($get_full_file_path, 'r');
            $rows = 0;
            $fileCount = 1;
            $out = null;
            while (!feof($in)) {
                if (($rows % $splitSize) == 0) {
                    if ($rows > 0) {
                        fclose($out);
                    }
                    $line = fgets(fopen($get_full_file_path, 'r'));
                    if ($line && $fileCount == 1) {
                        /*
                         * Lets separate the header from the other files so that we can append later on
                         */
                        Log::info('Info - ', ['Extracting Header File']);
                        $headers = 'header.csv';
                        $fileName = $chunk_file_directory . $headers;
                        $out = fopen($fileName, 'w');
                        $data = fgetcsv($in);
                        fputcsv($out, $data);

                    }
                    /*
                     * Now we create the bite sized files from the larger file
                     */
                    $fileCount++;
                    $fileName = $chunk_file_directory . $fileCount . ".csv";
                    Log::info('Info - ', ['Building Chunk Files - ' . $fileName]);
                    $out = fopen($fileName, 'w');
                }
                $data = fgetcsv($in);
                if ($data) {
                    fputcsv($out, $data);
                }
                $rows++;
            }
        }catch (Exception $e){
            Log::alert('Error', ['Was not able to parse CSV',$e->getCode(),$e->getMessage()]);
        }
    }
}
