<?php

namespace Drupal\gittest\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ExelaGitController.
 */
class ExelaGitController extends ControllerBase {


  protected $owner;

  protected $username;

  protected $password;

  protected $client;
  
  protected $repos;

  protected $orgs;

  /**
   * Getrepo.
   *
   * @return string
   *   Return Hello string.
   */
  public function getRepo() {

    $repositories = $this->client->api('user')->repositories($this->owner);
    

    /*echo "<pre>", print_r($repositories);*/
  
    $count = 0;
    foreach($repositories as $repo){

      
       $count++;

       $str.= "Repo".$count."=".$repo['name']."URL=<a href=".$repo['html_url'].">".$repo['html_url']."</a><br>";

    }

    //return $repositories;
    return [
      '#type' => 'markup',
      '#markup' => $str
    ];
  }

  public function __construct() {

    $this->client   =  new \Github\Client();

    $this->getConfig();

}

protected function getConfig() {

      $config = \Drupal::config('gittest.config');
      //echo "<pre>", print_r($config);
      // Will print 'Hello'.
      //print $config->get('message');
      // Will print 'en'.
      //print $config->get('langcode');
     

      $this->username = $config->get('username');

      $this->password = $config->get('password');
      
      $this->owner = $config->get('owner');

      $this->owner = 'jyotideshmukh';

      $this->username = 'jyotipawar2003@gmail.com';

      $this->password = 'Avaghadaahe76$';

      
        
}


public function getRepoCommits($reponame) {

}

public function createRepo($teamname) {

  //echo $teamname;die;

  //$repo ='myestcreation';

  $repositories = $this->client->api('user')->repositories($this->owner);

  $this->client->authenticate($this->username, $this->password, \Github\Client::AUTH_HTTP_PASSWORD);
  
  $repo = $this->client->api('repo')->create($teamname, 'Hackthon', 'https://github.com/jyotideshmukh/', true);
  
  return [
    '#type' => 'markup',
    '#markup' => $repo['name']." created succesfully"
  ];
}


public function pullRepo($teamname){

  $this->client->authenticate($this->username, $this->password, \Github\Client::AUTH_HTTP_PASSWORD);

  $pullRequest = $this->client->api('pull_request')->create($this->owner, $teamname, array(
    'base'  => 'master',
    'head'  => 'origin',
    'issue' => 15
));

}



}
