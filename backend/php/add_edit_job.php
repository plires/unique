<?php
  require_once('con.php');

    $position = $_POST['position'];
    $location = $_POST['location'];
    $job_function = $_POST['job_function'];
    $employment_type = $_POST['employment_type'];
    $description = $_POST['description'];

    if ($_POST['link'] != '') {
      $link = $_POST['link'];
    } else {
      $link = 0;
    }

  if ( isset($_POST['edit']) == true ) { // Si es edicion

    $id = $_POST['id'];
    $sql = "
    UPDATE jobs 
    SET 
    position = '" .$position. "', 
    location = '" .$location. "', 
    job_function = '" .$job_function. "', 
    employment_type = '" .$employment_type. "',
    description = '" .$description. "',
    link = '" .$link. "'
    WHERE id = ".$id." ";

    $stmt = $db->prepare($sql);
    $job = $stmt->execute();
    
    echo json_encode($job);

  } else { // Si es un registro nuevo

    $sql = "INSERT INTO jobs( position, location, job_function, employment_type, description, link, status) VALUES( :position, :location, :job_function, :employment_type, :description, :link, :status)";

    $stmt = $db->prepare($sql);

    $result = $stmt->execute(
      array(
        'position' => $position,
        'location' => $location,
        'job_function' => $job_function,
        'employment_type' => $employment_type,
        'description' => $description,
        'link' => $link,
        'status' => 1
      )
    );

    echo json_encode($result);
  	
  }

?>