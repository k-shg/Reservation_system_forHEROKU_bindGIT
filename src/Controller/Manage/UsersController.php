<?php
namespace App\Controller\Manage;
use App\Controller\Manage\AppController;
use App\Model\Entity\User;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['logout', 'add']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Reservations']
        ]);
        $this->set('user', $user);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }

        //roleのための配列を作成
        $rolesArray = [
            User::ADMIN =>__('Manager'),
            User::NORMAL =>__('Normal')
        ];
        $this->set(compact('user','rolesArray'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $password = $user->password;
            $getData = $this->request->getData();

            //空入力のときはパスワード更新せず
            if (empty($this->request->getData('password'))){
                unset($getData['password']);
            }
            $user = $this->Users->patchEntity($user, $getData);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));

        }
        //roleのための配列を作成
        $rolesArray = [
            User::ADMIN =>__('Manager'),
            User::NORMAL =>__('Normal')
        ];

        //stop用の配列を作成
        $stopArray = [
            true => __("stop"),
            false => __('open')
        ];
        $this->set(compact('user','rolesArray', 'stopArray'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();

            //凍結されたアカウントはログイン不可
            if ($user['stop'] === true){
                $this->Flash->error(__('This account has been frozen'));
                return;
            }
            if ($user) {
                $this->Auth->setUser($user);
                if ($user['role'] === User::ADMIN){
                    $this->Flash->success(__("Your login is executed"));
                    return $this->redirect(['prefix'=> 'manage', 'controller' => 'Reservations' , 'action' => 'index']);
                }
                $this->Flash->success(__("Your login is executed"));
                return $this->redirect(['prefix'=> false, 'controller' => 'Reservations' , 'action' => 'index']);
            }
            $this->Flash->error(__('User name or password is invalid'));
        }
    }

    public function logout()
    {
        $this->Flash->success(__("Your logout is executed"));
        return $this->redirect($this->Auth->logout());
    }
}
