<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
declare(strict_types = 1);

class LoginController extends Phalcon\Mvc\Controller
{
    public function indexAction()
    {
    }
    public function validateAction()
    {
        if (true === $this->request->isPost()) {
            $name = $this->request->getPost('name');
            $password = $this->request->getPost('password');

            $users = Users::query()
                ->where('name = :name:')
                ->andWhere('password = :pass:')
                ->bind(
                    [
                        'name' => $name,
                        'pass' => $password,
                    ]
                )
                ->execute()
            ;
            if ($users->count()) {
                $user = $users[0];
                $this->session->set('user_name', $user->name);

                $homes = Homes::query()
                    ->where('owner = :owner:')
                    ->bind(
                        [
                            'owner' => $user->role,
                        ]
                    )
                    ->execute()
                ;
                if ($homes->count()) {
                    $home = $homes[0];
                    $this->response->redirect($home->model);
                    return;
                }
            }
        }
        $this->response->redirect('');
    }
}
