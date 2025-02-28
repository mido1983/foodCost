<?php
class FileUploader {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $fileName;
    private $fileType;
    private $fileTmpName;
    private $fileError;
    private $fileSize;
    private $newFileName;
    
    public function __construct($uploadDir = 'uploads', $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'], $maxFileSize = 5242880) {
        // Ensure upload directory exists and is writable
        $this->uploadDir = rtrim(UPLOADS_PATH, '/') . '/' . trim($uploadDir, '/') . '/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        $this->allowedTypes = $allowedTypes;
        $this->maxFileSize = $maxFileSize;
    }
    
    public function setFile($fileData) {
        $this->fileName = $fileData['name'];
        $this->fileType = $fileData['type'];
        $this->fileTmpName = $fileData['tmp_name'];
        $this->fileError = $fileData['error'];
        $this->fileSize = $fileData['size'];
        
        return $this;
    }
    
    public function upload() {
        // Check for errors
        if ($this->fileError !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $this->getUploadErrorMessage($this->fileError));
        }
        
        // Check file size
        if ($this->fileSize > $this->maxFileSize) {
            throw new Exception('File is too large. Maximum size is ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }
        
        // Check file type
        if (!in_array($this->fileType, $this->allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }
        
        // Generate a unique file name
        $fileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $basename = pathinfo($this->fileName, PATHINFO_FILENAME);
        
        // Transliterate non-Latin characters to Latin
        $basename = $this->transliterate($basename);
        
        // Create safe filename: timestamp + sanitized name
        $this->newFileName = time() . '_' . $basename . '.' . $fileExtension;
        $targetFile = $this->uploadDir . $this->newFileName;
        
        // Move the file
        if (!move_uploaded_file($this->fileTmpName, $targetFile)) {
            throw new Exception('Failed to move uploaded file. Check permissions.');
        }
        
        return $this->newFileName;
    }
    
    public function getUploadedFileUrl() {
        if (!$this->newFileName) {
            return null;
        }
        
        $relativePath = str_replace(UPLOADS_PATH, '', $this->uploadDir);
        return UPLOADS_URL . $relativePath . $this->newFileName;
    }
    
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    private function transliterate($string) {
        // Transliteration table for Cyrillic characters
        $table = [
            'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh',
            'з'=>'z', 'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o',
            'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts',
            'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch', 'ъ'=>'', 'ы'=>'y', 'ь'=>'', 'э'=>'e', 'ю'=>'yu',
            'я'=>'ya'
        ];
        
        // Convert to lowercase
        $string = mb_strtolower($string, 'UTF-8');
        
        // Transliterate
        $string = strtr($string, $table);
        
        // Replace all non-alphanumeric characters with dashes
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        
        // Remove leading/trailing dashes
        $string = trim($string, '-');
        
        return $string;
    }
} 