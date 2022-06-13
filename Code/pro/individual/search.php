<?php
if (!isset($file_access)) die("Direct File Access Denied");
?>
<?php

$me = $_SESSION['user_id'];

?>
<br>



<div class="content">
    
<form action="<?php echo $_SERVER['PHP_SELF'].'?page=search' ?>" method="post">
    <div class="form-row">
        <div class="col">
        <input type="text" class="form-control" placeholder="Start" name="start">
        </div>
        <div class="col">
        <input type="text" class="form-control" placeholder="Destination" name="stop">
        </div>
        <div class="col">
        <input type="date" class="form-control" name = "calendar">
        </div>
        <div class="col">
        <button type="submit" class="btn btn-danger btn-lg" name="search" data-toggle="modal"> <i class="fa fa-search nav-icon"></i> Search </button>
        </div>
    </div>
    </form>

<br>



    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
        
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><b>Book Transport Tickets</b></h3>
                </div>

        <?php
                if (isset($_POST['search'])) {
        ?>


                <div class="card-body">
                    <table id="example1" style="align-items: stretch;"
                        class="table table-hover w-100 table-bordered table-striped<?php //
                                                                                                                                    ?>">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Agency </th>
                                <th> Vehicle </th>
                                <th>Route</th>
                                <th>Status</th>
                                <th>Date/Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            if( !empty($_POST['start']) && !empty($_POST['stop'])  && !empty($_POST['calendar'])){
                        
                                $row = searchThree($_POST['start'],$_POST['stop'],$_POST['calendar']);
                            }else if( !empty($_POST['start']) && !empty($_POST['stop'])){
                                
                                $row = searchTwo($_POST['start'],$_POST['stop']);
                            }else if ( !empty($_POST['start'])){
                                
                                $row = searchStart($_POST['start']);

                            }else if ( !empty($_POST['stop'])){
                                
                                $row = searchDestination($_POST['stop']);

                            } else {
                                
                                
                                
                                $row = searchDate($_POST['calendar']);
                                
                            }
                            if ($row->num_rows < 1) echo "<div class='alert alert-danger' role='alert'>
                            Sorry, There are no schedules with the searched data! Please make another search.
                          </div>";
                            $sn = 0;

                            while ($fetch = $row->fetch_assoc()) {
                                //Check if the current date is same with Database scheduled date
                                $db_date = $fetch['date'];
                                if ($db_date == date('d-m-Y')) {
                                    //Oh yes, so what should happen?
                                    //Check for the time. If there is still about an hour left, proceed else, skip this data
                                    $db_time = $fetch['time'];
                                    $current_time = date('H:i');
                                    if ($current_time >= $db_time) {
                                        continue;
                                    }
                                }
                                $id = $fetch['id']; ?><tr>
                                
                                <td><?php echo ++$sn; ?></td>
                                
                                <td><?php echo $res=getAgencyName($fetch['agency_id']);?></td>
                                
                                <td> <?php echo $vehcileName = getVehicleName($fetch['train_id'])?> </td>

                                <td><?php echo $fullname =  getRoutePath($fetch['route_id']);?></td>
                                <td><?php $array = getTotalBookByType($id);
                                        echo ($max_first = ($array['first'] - $array['first_booked'])), " Seat(s) Available for First Class" . "<hr/>" . ($max_second = ($array['second'] - $array['second_booked'])) . " Seat(s) Available for Second Class";
                                        ?></td>
                                <td><?php echo $fetch['date'], " / ", formatTime($fetch['time']); ?></td>
                                
                                <td>
                                <!--FOLLOW UNFOLLOW SUBMISSION-->                           
                                <?php
                                            
                                            if(isset($_POST['fllw'.$id])) {

                                                follow($fetch['agency_id']);

                                                load($_SERVER['PHP_SELF'] . "?page=reg");
                                            }

                                            if(isset($_POST['unfllw'.$id])){
                                                unfollow($fetch['agency_id']);
                                                load($_SERVER['PHP_SELF'] . "?page=reg");

                                            }
                                           
                                ?>

                                    <form method="post">
                                            
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#book<?php echo $id ?>">
                                        Book
                                    </button>
                                    <?php

                                            if(isFollowing($fetch['agency_id'])){
                                                
                                                ?>
                                                
                                    <button type="submit" class="btn btn-warning" name="<?php echo 'unfllw'.$id;?>" data-toggle="modal">
                                        Unfollow
                                    </button>
                                    <?php
                                            } 
                                            else {
                                    ?>
                                            
                                    <button type="submit" class="btn btn-success" name="<?php echo 'fllw'.$id;?>" data-toggle="modal">
                                        Follow
                                    </button>
                                    
                                    <?php } ?>
                                    
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="book<?php echo $id ?>">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Book For <?php echo $fullname;


                                                                                    ?> &#128642;</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">


                                            <form action="<?php echo $_SERVER['PHP_SELF'] . "?loc=$id" ?>"
                                                method="post">
                                                <input type="hidden" class="form-control" name="id"
                                                    value="<?php echo $id ?>" required id="">

                                                <p>Number of Tickets (If you are the only one, leave as it is) :
                                                    <input type="number" min='1' value="1"
                                                        max='<?php echo $max_first >= $max_second ? $max_first : $max_second ?>'
                                                        name="number" class="form-control" id="">
                                                </p>
                                                <p>
                                                    Class : <select name="class" required class="form-control" id="">
                                                        <option value="">-- Select Class --</option>
                                                        <option value="first">First Class ($
                                                            <?php echo ($fetch['first_fee']); ?>)</option>
                                                        <option value="second">Second Class ($
                                                            <?php echo ($fetch['second_fee']); ?>)</option>
                                                    </select>
                                                </p>
                                                <input type="submit" name="submit" class="btn btn-success"
                                                    value="Proceed">

                                            </form>

                                        </div>
                                        <!-- /.modal-content -->
                                    </div>
                                    <!-- /.modal-dialog -->
                                </div>
                                <!-- /.modal -->
                                <?php
                            }
                                ?>

                        </tbody>
                        
                    </table>
                    <?php 
                }
                    
                    ?>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>

    </form>

</div>



