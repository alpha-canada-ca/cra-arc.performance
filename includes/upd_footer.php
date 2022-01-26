<?php ?>

<footer>
  <div class="d-flex flex-row-reverse mt-5 pb-4">
    <div class="col-lg-2 col-md-3 col-sm-4 col-4"><img class="img-fluid" src="./assets/img/canada-black-30mm.png" alt="Government of Canada"></div>
  </div>
</footer>

</div>

<script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/datatables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>


<script type="text/javascript" src="js/sidebar.js"></script>

<script>
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
  })
</script>

<script>

  $(document).ready(function() {
      $("#toptask, #toptask2").DataTable({
          "paging":   false,
          //"ordering": true //default value
          //"order": [[ 1, "asc" ]],
          "order": [],
          "searching": false,
          "info": false
        });

      $("#pages_dt").DataTable({
        "searching": true
      });

      // EXPORT BUTTONS ON DATA TABLE
      var tbl_buttons =  $('.datatables-with-buttons').DataTable( {
        "paging":   false,
        //"ordering": true //default value
        //"order": [[ 1, "asc" ]],
        "order": [],
        "searching": false,
        "info": false,
        //dom: 'Bfrtip',
        //dom: '<"btn">Bt',
        dom: 'Bt',
        //dom: '<"btn btn-outline-dark"B>t',
        // buttons: [
        //     //'copy', 'csv', 'excel', 'pdf', 'print'
        //     'csv', 'excel', 'pdf', 'print'
        // ],
        buttons: [
            //{ extend: 'copy', className: 'btn btn-outline-primary glyphicon glyphicon-duplicate' },
            { extend: 'csv', className: 'btn btn-outline-primary glyphicon glyphicon-save-file' },
            { extend: 'excel', className: 'btn btn-outline-primary glyphicon glyphicon-list-alt' },
            { extend: 'pdf', className: 'btn btn-outline-primary glyphicon glyphicon-file' },
            { extend: 'print', className: 'btn btn-outline-primary glyphicon glyphicon-print' },
            // for the column visibility one, add (create) a class that will affect the unordered list in teh dropdown...
            //{ extend: 'colvis', className: 'btn btn-outline-primary' },
        ],
        // initComplete: function () {
        //     var btns = $('.dt-button');
        //     btns.addClass('btn btn-outline-primary');
        //     btns.removeClass('dt-button');
        //
        // }
      } );

      // tbl_buttons.buttons().container()
      //   .appendTo( '#example_wrapper .col-md-6:eq(0)' );

      // $("#pages_dt").DataTable();

        // Datatable filter for #pages_dt_filter table
      //$('#pages_dt_filter').DataTable();

      //RUN THIS CODE om pages where "pages_dt_filter" exists
      //----------------------------------------------------------
      if($("#pages_dt2_filter").length >0) {

            $("#pages_dt2_filter").DataTable({
              "searching": true
            });

            //Get a reference to the new datatable
            var table = $("#pages_dt2_filter").DataTable();

            //Take the category filter drop down and append it to the datatables_filter div.
            //You can use this same idea to move the filter anywhere withing the datatable that you want.
            $("#pages_dt2_filter_filter.dataTables_filter").append($("#categoryFilter"));

            //Get the column index for the Category column to be used in the method below ($.fn.dataTable.ext.search.push)
            //This tells datatables what column to filter on when a user selects a value from the dropdown.
            //It's important that the text used here (Category) is the same for used in the header of the column to filter
            var categoryIndex = 0;
            $("#pages_dt2_filter th").each(function (i) {
              if ($($(this)).html() == "Category") {
                categoryIndex = i; return false;
              }
            });

            //Use the built in datatables API to filter the existing rows by the Category column
            $.fn.dataTable.ext.search.push(
              function (settings, data, dataIndex) {
                var selectedItem = $('#categoryFilter').val()
                var category = data[categoryIndex];
                if (selectedItem === "" || category.includes(selectedItem)) {
                  return true;
                }
                return false;
              }
            );

            //Set the change event for the Category Filter dropdown to redraw the datatable each time
            //a user selects a new filter.
            $("#categoryFilter").change(function (e) {
              table.draw();
            });

            table.draw();


        }



  });
</script>



</body>
</html>
