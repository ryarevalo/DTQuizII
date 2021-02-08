<?php

class FileUploadController extends AppController {
	public function index() {
		$this->set('title', __('File Upload Answer'));

		if ($this->request->is('POST')){
            if (!empty($this->request->data)){
                if(!empty($this->request->data['FileUpload']['file']['name'])) {
                    $file = $this->request->data['FileUpload']['file'];

                    if (is_file($file['tmp_name'])){
                        // check file type and mime type
                        $extension = substr(strtolower(strrchr($file['name'], '.')), 1);
                        if ($extension == 'csv' && $file['type'] == 'text/csv') {
                           
                            $filedata = file($file['tmp_name']);
                            $rows = preg_split( '/\r\n|\r|\n/', $filedata[0]);

                            $datatosave = array();
                            $header = explode(',', strtolower($rows[0]));
                            
                            // save it in one array 
                            for ($i = 1; $i < count($rows); $i++) {
                                $datatosave[$i] = array();
                                $tempdata = explode(',', $rows[$i]);

                                for ($j = 0; $j < count($tempdata); $j++) {
                                    $datatosave[$i][$header[$j]] = $tempdata[$j];
                                    $datatosave[$i]['created'] = date('Y-m-d H:i:s'); //  date time
                                }
                            }


                            try{
								$this->FileUpload->saveAll($datatosave);
							}
							catch(Exception $e){
								echo $e->getMessage();
							}
                        }
                    }
                    else {
                        $this->setFlash('File Extension should be .csv');
                    }
                }
            }
        }

		$file_uploads = $this->FileUpload->find('all');
		$this->set(compact('file_uploads'));
	}
}