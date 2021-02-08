<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
require ROOT.DS.'vendor'.DS.'phpoffice/phpspreadsheet/src/Bootstrap.php';

class MigrationController extends AppController{

    public function q1(){

        $this->setFlash('Question: Migration of data to multiple DB table');

        $this->loadModel('Member');
        $this->loadModel('Transaction');
        $this->loadModel('TransactionItem');

        if ($this->request->is('POST')){
            if (!empty($this->request->data)){
                if(!empty($this->request->data['MigrationFile']['file']['name'])) {
                    $file = $this->request->data['MigrationFile']['file'];

                    if (is_file($file['tmp_name'])){
                        
                        $lastMember = $this->Member->find('first', array('order' => array('Member.id' => 'DESC'), 'recursive' => -1))['Member']['id'];
                        $currMemberID = $lastMember;
                        $lastTransaction = $this->Transaction->find('first', array('order' => array('Transaction.id' => 'DESC'), 'recursive' => -1))['Transaction']['id'];
                        $currTransactionID = $lastTransaction;

                        // init array
                        $memberToSave = array();
                        $transactionToSave = array();
                        $transactionItemToSave = array();

                        // check file extension
                        $extension = substr(strtolower(strrchr($file['name'], '.')), 1);
                        if ($extension == 'xlsx') {
                            $reader = IOFactory::createReader("Xlsx");
                            $spreadsheet = $reader->load($file['tmp_name']);
                            $sheetdata = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);
                            $total_item = count($sheetdata);

                            for ($i = 2; $i <= $total_item; $i++) {
                                $memberNo = explode(' ', $sheetdata[$i]['D']);

                                $memberType = $memberNo[0];
                                $memberNo = $memberNo[1];
                                $memberName = $sheetdata[$i]['C'];

                                $curr_member = $this->Member->find('first', array(
                                    'conditions' => array(
                                        'Member.type' => $memberType,
                                        'Member.no' => $memberNo,
                                        'Member.name' => $memberName,
                                    ),
                                    'recursive' => -1,
                                ));
                                if (empty($curr_member)) {
                                    $memberToSave[$i]['type'] = $memberType;
                                    $memberToSave[$i]['no'] = (int)$memberNo;
                                    $memberToSave[$i]['name'] = $memberName;
                                    $memberToSave[$i]['company'] = $sheetdata[$i]['F'];

                                    $lastMember++;
                                    $currMemberID = $lastMember;
                                }
                                else {
                                    $currMemberID = $curr_member['Member']['id'];
                                }

								//debug($memberToSave); 

                                $receipt_no = $sheetdata[$i]['I'];

                                $currentTransaction = $this->Transaction->find('first', array(
                                    'conditions' => array(
                                        'Transaction.receipt_no' => $receipt_no,
                                    ),
                                    'recursive' => -1,
                                ));
                                if (empty($currentTransaction)) {
                                    $transactionToSave[$i]['member_id'] = $currMemberID;
                                    $transactionToSave[$i]['member_name'] = $sheetdata[$i]['C'];
                                    $transactionToSave[$i]['member_paytype'] = $sheetdata[$i]['E'];
                                    $transactionToSave[$i]['member_company'] = $sheetdata[$i]['F'];

                                    $date = date('Y-m-d' ,strtotime($sheetdata[$i]['A']));
                                    $transactionToSave[$i]['date'] = $date;
                                    $date_array = explode('/', $sheetdata[$i]['A']);
                                    $transactionToSave[$i]['year'] = $date_array[2];
                                    $transactionToSave[$i]['month'] = $date_array[0];

                                    $transactionToSave[$i]['ref_no'] = $sheetdata[$i]['B'];
                                    $transactionToSave[$i]['receipt_no'] = $receipt_no;
                                    $transactionToSave[$i]['payment_method'] = $sheetdata[$i]['G'];
                                    $transactionToSave[$i]['batch_no'] = $sheetdata[$i]['H'];
                                    $transactionToSave[$i]['cheque_no'] = $sheetdata[$i]['J'];
                                    $transactionToSave[$i]['payment_type'] = $sheetdata[$i]['K'];

                                    $transactionToSave[$i]['subtotal'] = $sheetdata[$i]['M'];
                                    $transactionToSave[$i]['tax'] = $sheetdata[$i]['N'];
                                    $transactionToSave[$i]['total'] = $sheetdata[$i]['O']; 

                                    $lastTransaction++;
                                    $currTransactionID = $lastTransaction;
                                }
                                else {
                                    $currTransactionID = $currentTransaction['Transaction']['id'];
                                }

                                $currentTransaction_item = $this->TransactionItem->find('first', array(
                                    'conditions' => array(
                                        'TransactionItem.transaction_id' => $currTransactionID,
                                    ),
                                    'recursive' => -1,
                                ));
                                if (empty($currentTransaction_item)) {
                                    $transactionItemToSave[$i]['transaction_id'] = $currTransactionID;

                                    $transactionItemToSave[$i]['description'] = "Being Pament for:".$sheetdata[$i]['K'];
                                    $transactionItemToSave[$i]['quantity'] = 1;
                                    $transactionItemToSave[$i]['unit_price'] = $sheetdata[$i]['M'];
                                    $transactionItemToSave[$i]['sum'] = $transactionItemToSave[$i]['quantity'] * $transactionItemToSave[$i]['unit_price'];

                                    $transactionItemToSave[$i]['table'] = "Member";
                                    $transactionItemToSave[$i]['table_id'] = $currMemberID;;
                                }
                                else {

                                }
                            }

                            if (empty($memberToSave)){
                                //echo "no added member"; 
                            } else {
								//debug($memberToSave); die();
                                if ($this->Member->saveAll($memberToSave)){
									//echo count($memberToSave).' members added!';
                                }
                                else {
                                    //echo "no added member"; 
                                }
                            }

                            if (empty($transactionToSave)){
								//echo 'no transaction added';
                            } else {
					
                               try{
								if ($this->Transaction->saveAll($transactionToSave)){
									//echo count($transactionToSave).' trans added!';
                                }
                                else {
									//echo 'no transaction added';
                                }
							   }catch(Exception $e){
								   echo $e->getMeesage();
							   }
                            }

                            if (empty($transactionItemToSave)) {
								//echo 'no transactionItem added';
                            } else {
                                if ($this->TransactionItem->saveAll($transactionItemToSave)){
									//echo count($transactionItemToSave).' transactionItems added!';
                                }
                                else {
									//echo 'no transactionItem added';
                                }
                            }

                        }
                    }
                    else {
                        $this->setFlash('File Extension should be .xlsx');
                    }
                }
            }
        }

        // 			$this->set('title',__('Question: Please change Pop Up to mouse over (soft click)'));
    }

    public function q1_instruction(){

        $this->setFlash('Question: Migration of data to multiple DB table');



        // 			$this->set('title',__('Question: Please change Pop Up to mouse over (soft click)'));
    }

}