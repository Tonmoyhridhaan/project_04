<?php 
    session_start();
    //authorization
    if(!$_SESSION['username']){
      session_destroy();
      header('Location: ../index.php');
    }
    else if($_SESSION['username'] && $_SESSION['role'] != 'teacher'){
      session_destroy();
      header('Location: ../unauthorised_user.php');
    }
    $id = $_SESSION['id'];
    include '../include/connection.php';
?>

<!DOCTYPE html>
  <html lang="en">
  <head>
    <title>Teacher (marks assignation)</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../include/link.php' ?>
  </head>
  <body>
    <section id="container" class="">
      <?php include '../include/teacher_navbar.php' ?>
      <?php include '../include/teacher_sidebar.php' ?>
      <section id="main-content">
        <section class="wrapper">
          <div class="row">
            <div class="col-lg-12">
              <h3 class="page-header"><i class="fa fa-pencil" aria-hidden="true"></i>Marks assignation</h3>
              <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="dashboard.php">Home</a></li>
                <li><i class="fa fa-pencil" aria-hidden="true"></i>Marks Assign</li>
              </ol>
            </div>
          </div>
          <form action="#" method="post">
            <div class="row">
              <div class="col-md-1"></div>
              <div class="col-md-5 portlets">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <div class="pull-left">Select Available Session</div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="panel-body">
                    <div class="padd">
                      <div class="card-body">
                        <div class="form-group">
                          <select class="form-control" name="session" id="session">
                            <option value=" ">-select session-</option>
                            <?php 
                              $qry = "SELECT DISTINCT sessions.id, sessions.name FROM sessions, teacher_assign WHERE teacher_assign.teacher_id = $id AND teacher_assign.session_id = sessions.id";
                              $r = mysqli_query($conn, $qry);
                              while($row4 = mysqli_fetch_array($r)){ ?>
                                <option value="<?php echo $row4['id']; ?>"><?php echo $row4['name']; ?></option> 
                                <?php 
                              }
                            ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-5 portlets" id="course_list">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <div class="pull-left">Select Available Course</div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="panel-body">
                    <div class="padd">
                      <div class="card-body">
                        <div class="form-group">
                          <select class="form-control" name="course" id="course">
                            <!-- <option value=" ">-select course-</option>  -->
                            <!-- ajax -->
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row" id="student_marks">
              <div class="col-sm-1"></div>
              <div class="col-sm-10">
                <section class="panel">
                  <header class="panel-heading">Enrolled Students</header>
                  <table class="table" id="student_dist">
                    <thead>
                      <tr>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>  
                      </tr>
                    </tbody>
                  </table>
                </section>
              </div>
              <div class="col-sm-5"></div>
              <div class="col-sm-7">
                <div class="form-group">
                  <button name="submit" id="submit" class="btn btn-primary sub">Save Progress</button>
                </div>
              </div>
            </div>
          </form>
        </section>
      </section>
    </section>
    <script>
      $(document).ready(function() {
        //hiding course section
        $('#course_list').hide();
        $('#student_marks').hide();
        $('#session').change(function(){
          var session = $('#session').val();
          if(session != " "){
            $('#course_list').show();
            $('#course').change(function(){
              var course = $('#course').val();
              if(course != " "){ $('#student_marks').show(); }
              else { $('#student_marks').hide(); }
            });
          }
          else{ $('#course_list').hide(); $('#student_marks').hide(); }
        });
      });
    </script>
    <script>
      $(document).ready(function() {
        $("#session").change(function() {
          var session_id = $("#session").val();
          //using ajax
          $.ajax({
            url: "get_course_section.php",
            dataType: 'json',
            data: {
              "session_id" : session_id
            },
            success: function(data) {
              $("#course").html('<option value=" ">-select course-</option>');
              for(i=0; i<data.length;i++){
                var x = '<option value="'+data[i].course_id+'|'+data[i].section_id+'">'+data[i].course_name+'&emsp;&emsp;&emsp;'+data[i].section_name+'</option>';
                $("#course").append(x);
              }
            }
          });
        });
      });
    </script>
    <script>
      $(document).ready(function() {
        $("#course").change(function() {
          var cnt = 0;
          var course_id = $("#course").val().split('|')[0];
          var section_id = $("#course").val().split('|')[1];
          var session_id = $("#session").val();
          //alert(course_id+'-'+section_id+'-'+session_id);
          // using ajax
          $.ajax({
            url: "get_students_dist_form.php",
            dataType: 'json',
            data: {
              "course_id" : course_id,
              "session_id" : session_id,
              "section_id" : section_id
            },
            success: function(data) {
              console.log(data);
              $("#student_dist thead tr").html('\
                <th>#S/N</th>\
                <th>ID</th>\
                <th>Name</th>\
                ');
              for(i=0; i<data.length;i++){
                var x = '<th>'+data[i].catagory_name+' ('+data[i].marks+') '+'</th>';
                $("#student_dist thead tr").append(x);
                cnt++;
              }
              $("#student_dist thead tr").append('<th>Total</th>');
            }
          });

          $.ajax({
            url: "get_enrolled_students.php",
            dataType: 'json',
            data: {
              "course_id" : course_id,
              "session_id" : session_id,
              "section_id" : section_id
            },
            success: function(data) {
              console.log(data);
              $("#student_dist tbody tr").html(' ');
              for(i=0; i<data.length;i++){
                var x = '<tr><td>'+(i+1)+'</td>'+'<td>'+data[i].student_id+'</td>'+'<td>'+data[i].name+'</td>';
                var begin = parseInt(data[i].begin);
                var end = parseInt(data[i].end);
                var k = parseInt("0");
                for(j=begin; j<=end;j++){
                  x = x+'<td><input class="form-control input-sm m-bot15 marks" type="number" name="marks'+(k+1)+'[]" value="'+data[i][k]+'" id="marks"></td>';
                  k++;
                }
                
                $("#student_dist tbody ").append(x+'<td><output id="total_mark"></output></td><tr>');
              }
              $('#student_dist').on('input', '.marks', function(){
                var sum = 0;
                $('#student_dist .marks').each(function(){
                  var input_val = $(this).val();
                  if($.isNumeric(input_val)){
                    sum += parseFloat(input_val);
                  }
                });
                $('#total_mark').text(sum+'/100');
                if(sum > 100){ 
                  alert('MARKS LIMIT EXCEEDED!');
                  //$('#submit').prop('disabled', true);
                  //$('#add').prop('disabled', true);
                }
                else if(sum == 100){ 
                  //alert('Submit Now: total marks fixed'); 
                  //$('#submit').prop('disabled', false);
                  //$('#add').prop('disabled', true);
                }
                else { 
                  //$('#submit').prop('disabled', true); 
                  //$('#add').prop('disabled', false);
                }
              });
            }
          });
        });
      });
    </script>
    <?php include '../include/script.php' ?>
  </body>
</html>
<?php  ?>
<?php 
  if(isset($_POST['submit'])){
    if(!empty($_POST['check_list'])){
      $session_id = $_POST['session'];
      $id = $_SESSION['id'];

      $course_id=[];
      $section_id=[];
      $type_id=[];
      
      $i=0;
      foreach($_POST['check_list'] as $selected) {$course_id[$i] = $selected;$i++;}
      $i=0;
      foreach($_POST['section'] as $selected) {if($selected!=" "){$section_id[$i] = $selected;$i++;}}
      $i=0;
      foreach($_POST['type'] as $selected) {if($selected!=" "){$type_id[$i] = $selected;$i++;}}
      $n = count($course_id);
      //* one must choose a course in which a teacher is assigned 'Ex: check teacher assign'
      for($i=0;$i<$n;$i++){
        $qry = "SELECT * FROM teacher_assign WHERE section_id = $section_id[$i] AND course_id = $course_id[$i] AND session_id = $session_id AND status = 1"; // For later use 'AND status=0'
        $r = mysqli_query($conn, $qry);
        $row = mysqli_fetch_assoc($r);
        $teacher_id = $row['teacher_id'];
        //echo ' sec: '.$section_id[$i].' course: '.$course_id[$i].' type: '.$type_id[$i].' teacher: '.$teacher_id.'<br>'; //sidebar toggle korle printed value dekhabe :)
        $qry = "INSERT INTO enrollment(student_id,course_id,type_id,section_id,teacher_id,session_id,status) VALUES ($id, $course_id[$i], $type_id[$i], $section_id[$i], $teacher_id,$session_id,0)";
        if (mysqli_query($conn, $qry)){
          //echo "assigned\n";
          //enrollment table e inserted hobe.
          $qry1 = "SELECT * FROM num_dist WHERE teacher_id=$teacher_id AND section_id = $section_id[$i] AND course_id = $course_id[$i] AND session_id = $session_id"; 
          $sql1 = mysqli_query($conn, $qry1);

          while($row1 = mysqli_fetch_array($sql1)){
            $dist_id = $row1['id'];
            $qry2 = "INSERT INTO `marks_assign`(`student_id`, `teacher_id`, `course_id`, `section_id`, `session_id`, `dist_id`, `marks`) VALUES ($id, $teacher_id, $course_id[$i], $section_id[$i], $session_id, $dist_id, 0)";
            //echo $qry2;
            if (mysqli_query($conn, $qry2)){
              //echo "insert seccess\n";
            }
          }
          //marks_assign table e inserted hobe.
        }
      }
    }
  }
?>