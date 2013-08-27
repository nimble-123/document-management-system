<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 *
 *
 */
class File_model extends CI_Model {
	var $F_PATH;
	function __construct() {
		parent::__construct ();
		// upload ordner lokalisieren
		$this->F_PATH = realpath ( APPPATH . '../uploads/' );
	} 
	function do_upload() {
		// libary loading
		$config ['allowed_types'] = 'doc|docx|odt|pdf';
		$config ['upload_path'] = $this->F_PATH;
		$config ['max_size'] = '20480';
		$config ['max_filename'] = '100';
		// $config['max_width'] = '1024';
		// $config['max_height'] = '768';
		$this->load->library ( 'upload', $config );
	}
	function create_File($document_id) {
		//uploding libray loaden
		$this->do_upload();
		
		// uploading
		if ($this->upload->do_upload ( 'i_file' )) {
			// die info aus der hochgeladenen datei zugreifen
			$data = $this->upload->data ();
			
			// file original name
			$tmpName = $data ['raw_name'];
			// file Path
			$filePath = $data ['full_path'];
			//md5string erzeugen anhand fullpath
			$md5 = md5_file($filePath);
			// file endung
			$fileExt = $data ['file_ext'];
			// file name basteln, daf��r braucht man document_model
			$this->load->model ( 'document_model' );
			$document = $this->document_model->get_Document ( $document_id );
			
			// die zu speicherndem name zusammensetzen
			$fileName = $tmpName . "_" . $document->title . "_" . $document->project . $fileExt;
			
			$file = array (
					
					'file' => $filePath,
					
					'md5' =>  $md5,
					
					'name' => $fileName 
			);
			
			
			$query = $this->db->insert ( 'storage_file', $file );
			
			// kreuztabelle, um mit document zu verbinden
			if (! $query) {
				return 'Upload failed!';
			} else {
				$query = $this->db->query ( 'select last_insert_id() as last_id' );
				
				$row = $query->row ();
				
				$file_id = $row->last_id;
				
				$this->db->insert ( 'storage_document_has_file', array (
						
						'document_id' => $document_id,
						
						'file_id' => $file_id 
				) );
			}
			return true;
		} 
		//geht es schief dann erros abliefern
		else {
			return $this->upload->display_errors ();
		}
	}
	function download_File($id) {
		$this->db->where('id', $id);
		$file = $this->db->get('storage_file');
		if($file->num_rows() == 1 ) {
			$row = $file->row();
			
			//download starten
			$data = file_get_contents($row->file);
			$name = $row->name;
			
			force_download($name, $data);
		}	
	}
	function update_File() {
	}
	function delete_File() {
	}
}
/* End of file file_model.php */
/* Location: ./application/models/file_model.php */
