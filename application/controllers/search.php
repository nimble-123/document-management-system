<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Search
 */
class Search extends CI_Controller {

   /**
    * CI_Controller Konstruktor + check ob user eingelogt ist
    */
   function __construct() {
      parent::__construct();
      $this->is_logged_in();
   }

   /**
    * soll noch ausgelagert werden in einen helper
    */
   function is_logged_in() {
      $is_logged_in = $this->session->userdata('is_logged_in');

      if (!isset($is_logged_in) || $is_logged_in != TRUE) {
         redirect('login');
      }
   }

   /**
    * default index function
    */
   function index() {
      $data['view'] = 'search/search_view';
      $this->load->view('template/content', $data);
   }

   /**
    * erweiterte suche
    */
   function search_advanced() {
      $this->load->model('project_model');
      $this->load->model('classification_model');

      // projekte zur view laden
      $projects = $this->project_model->get_Project();
      if ($projects) {
         $data['projects'] = $projects;
      }

      // klassifizierung zur view laden
      $classifications = $this->classification_model->get_Classification();
      if ($classifications) {
         $data['classifications'] = $classifications;
      }

      $data['jQuery'] = TRUE;
      $data['view']   = 'search/search_advanced_view';
      $this->load->view('template/content', $data);
   }

   /**
    * anzeige der suchergebnisse
    */
   function show_result() {
      $this->load->model('document_model');

      if ($this->input->post('title') == '' && $this->input->post('keywords') == '') {
         $documents = $this->document_model->get_Documents();
      }
      elseif ($this->input->post('keywords') == '') {
         $title     = $this->input->post('title');
         $documents = $this->document_model->get_Documents($title, NULL);
      }
      elseif ($this->input->post('title') == '') {
         $keywords  = explode(',', str_replace(' ', '', $this->input->post('keywords')));
         $documents = $this->document_model->get_Documents(NULL, $keywords);
      }
      else {
         $title     = $this->input->post('title');
         $keywords  = explode(',', str_replace(' ', '', $this->input->post('keywords')));
         $documents = $this->document_model->get_Documents($title, $keywords);
      }

      // wenn Ergebnisse gefunden wurden
      if ($documents) {
         $data['documents'] = $documents;

         $data['view'] = 'search/result_view';
         $this->load->view('template/content', $data);
      }
      // wenn nichts gefunden wurde
      else {
         $data['error'] = 'No items found, search again with other input';
         $data['view']  = 'search/search_view';
         $this->load->view('template/content', $data);
      }
   }


   /**
    * ajax backend function welche vom js script gecalled wird
    */
   function show_Hint() {
      //getten
      $model   = $this->input->get('model');
      $entered = $this->input->get('entered');

      //entsprechenden model laden
      $this->load->model($model);

      // alle möglichen einträge nach dem model laden die mit dem übergebenen buchstaben beginnen
      switch ($model) {
         case "project_model":
            $hints = $this->project_model->getHints($entered);
            break;
         case "author_model":
            $hints = $this->author_model->getHints($entered);
            break;
      }


      // den response string formatieren so das in der view ein dropdown damit gefüllt werden kann
      $response = NULL;
      foreach ($hints->result() as $hint) {
         $response = $response . '<option value=' . $hint->id . '>' . $hint->name . '</option>';
      }

      echo $response;
   }

   /**
    * loads the popup view
    */
   function popup() {
      $this->load->model('document_model');

      $doc_id = $this->input->get('doc_id');

      $data['document'] = $this->document_model->get_Document($doc_id);

      //da die informationen aus kreuztabellen auch hergeholt werden muessen, loadet man hier die models
      $this->load->model('author_model');
      $this->load->model('keyword_model');
      $this->load->model('file_model');
      //und queryergebnis in $data einholen
      $data['authors']  = $this->author_model->get_Author_by_DocumentID($doc_id);
      $data['keywords'] = $this->keyword_model->get_Keyword_By_DocumentID($doc_id);
      $data['files']    = $this->file_model->get_File_By_DocumentID($doc_id);

      $this->load->view('search/popup_view', $data);
   }

   /**
    * file download function
    *
    * @param $file_id
    */
   function dl_file($file_id) {
      $this->load->model('file_model');
      //das file finden
      $this->file_model->download_File($file_id);
   }
}
/* End of file search.php */
/* Location: ./application/controllers/search.php */