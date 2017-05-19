<?php
/**
 * Mailchimp management
 * @package admin-mailchimp
 * @version 0.0.1
 * @uprade true
 */

namespace AdminMailchimp\Controller;

class ChimpController extends \AdminController
{
    private function _defaultParams(){
        return [
            'title'             => 'Mailchimp',
            'nav_title'         => 'Partner',
            'active_menu'       => 'partner',
            'active_submenu'    => 'mailchimp',
            'total'             => 0,
            'pagination'        => []
        ];
    }
    
    public function editAction(){
        if(!$this->user->login)
            return $this->show404();
        
        $id = $this->param->id;
        if(!$id && !$this->can_i->create_mailchimp)
            return $this->show404();
        elseif($id && !$this->can_i->update_mailchimp)
            return $this->show404();
        
        $old = null;
        $params = $this->_defaultParams();
        $params['title'] = 'Create New Subscriber';
        $params['error'] = '';
        $params['next']  = $this->req->get('ref') ?? $this->router->to('adminMailchimp');
        
        if($id){
            $params['title'] = 'Edit Subscriber';
            $object = $this->mc->member($id);
            if(!$object)
                return $this->show404();
            $old = $object;
        }else{
            $object = new \stdClass();
        }
        
        if(false === ($form=$this->form->validate('admin-mailchimp', $object)))
            return $this->respond('partner/mailchimp/edit', $params);
        
        
        $f_object = [
            'email_address' => $form->email,
            'email_type'    => 'html',
            'status'        => $form->status,
            'merge_fields'  => [
                'FNAME'         => '',
                'LNAME'         => ''
            ],
            'ip_signup'     => $this->req->getIP()
        ];
        
        if($form->first_name)
            $f_object['merge_fields']['FNAME'] = $form->first_name;
        if($form->last_name)
            $f_object['merge_fields']['LNAME'] = $form->last_name;
        
        $event = 'updated';
        if(!$id){
            $event = 'created';
            $this->mc->create($f_object);
        }elseif(!$this->mc->update($id, $f_object)){
            $params['error'] = $this->mc->last_error;
            return $this->respond('partner/mailchimp/edit', $params);
        }
        
        $this->fire('mailchimp:'. $event, $object, $old);
        
        $next = $this->req->getQuery('next');
        if($next)
            return $this->redirect($next);
        return $this->redirectUrl('adminMailchimp');
    }
    
    public function importAction(){
        if(!$this->user->login)
            return $this->show404();
        if(!$this->can_i->create_mailchimp)
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['title'] = 'Import Subscriber';
        $params['error'] = '';
        
        $next = $this->req->getQuery('ref');
        if(!$next)
            $next = $this->router->to('adminMailchimp');
        
        $params['next']  = $next;
        
        $object = new \stdClass();
        
        if(false === ($form=$this->form->validate('admin-mailchimp-import', $object)))
            return $this->respond('partner/mailchimp/import', $params);
        
        $emails = trim($form->emails);
        $emails = str_replace(["\r", "\n\r"], "\n", $form->emails);
        $emails = explode("\n", $emails);
        
        $ip = $this->req->getIP();
        
        $result = [];
        foreach($emails as $erow){
            $email = trim($erow);
            if(!$email)
                continue;
            
            $row = [
                'email_address' => $email,
                'email_type'    => 'html',
                'status'        => 'subscribed',
                'merge_fields'  => [
                    'FNAME'         => '',
                    'LNAME'         => ''
                ],
                'ip_signup'     => $ip
            ];
            
            if(strstr($email, ',')){
                $smail = explode(',', $email);
                $email = $smail[0];
                $row['email_address'] = trim($email);
                
                if(isset($smail[1])){
                    $smail = trim($smail[1]);
                    $smail = explode(' ', $smail);
                    $row['merge_fields']['FNAME'] = trim(array_shift($smail));
                    if(isset($smail[0]))
                        $row['merge_fields']['LNAME'] = implode(' ', $smail);
                }
            }
            
            if(!$row['email_address']){
                $this->form->setError('emails', 'Row `' . $erow . '` contain no email');
                return $this->respond('partner/mailchimp/import', $params);
            }
            
            if(!filter_var($row['email_address'], FILTER_VALIDATE_EMAIL)){
                $this->form->setError('emails', '`' . $row['email_address'] . '` Is not valid email address');
                return $this->respond('partner/mailchimp/import', $params);
            }
            
            if($row)
                $result[] = $row;
        }
        
        if(!$this->mc->create_batch($result)){
            $this->form->setError('emails', '`' . $this->mc->last_error);
            return $this->respond('partner/mailchimp/import', $params);
        }
        
        $this->fire('mailchimp:imported', $result);
        
        $this->redirect($next);
    }
    
    public function indexAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        if(!$this->can_i->read_mailchimp)
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['emails'] = [];
        $params['error'] = '';
        $params['total'] = 0;
        $params['next']  = $this->req->url;
        
        if(!$this->setting->mailchimp_app_key || !$this->setting->mailchimp_list_id)
            $params['error'] = 'Please fill site setting mailchimp_app_key and mailchimp_list_id';
        else{
            $page = $this->req->getQuery('page');
            if(!$page)
                $page = 1;
            $rpp = 20;
            $cond = [];
            $total = 0;
            
            // find
            if($this->req->getQuery('email')){
                $cond = [
                    'query' => $this->req->getQuery('email')
                ];
                $emails = $this->mc->search($cond, $rpp, $page);
                if(!$emails)
                    $params['error'] = $this->mc->last_error;
                else{
                    $total = $emails->total;
                    $rpp   = $emails->total;
                    $params['emails']= $emails->items;
                }
                
            }else{
                // filter 
                if($this->req->getQuery('status'))
                    $cond['status'] = $this->req->getQuery('status');
                
                $emails = $this->mc->get($cond, $rpp, $page);
                if(!$emails)
                    $params['error'] = $this->mc->last_error;
                else{
                    $total = $emails->total;
                    $params['emails']= $emails->items;
                }
            }
            
            if($rpp < $total)
                $params['pagination'] = \calculate_pagination($page, $rpp, $total, 10, $cond);
            $params['total'] = $total;
        }
        
        $this->form->setForm('admin-mailchimp-index');
        
        return $this->respond('partner/mailchimp/index', $params);
    }
    
    public function removeAction(){
        if(!$this->user->login)
            return $this->show404();
        if(!$this->can_i->remove_mailchimp)
            return $this->show404();
        
        $id = $this->param->id;
        
        $object = $this->mc->member($id);
        if(!$object)
            return $this->show404();
            
        $this->fire('mailchimp:deleted', $object);
        $this->mc->remove($id);
        
        $next = $this->req->getQuery('ref');
        if($next)
            return $this->redirect($next);
        
        return $this->redirectUrl('adminQuote');
    }
}