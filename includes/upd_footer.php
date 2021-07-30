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
<script type="text/javascript" src="js/sidebar.js"></script>

<script>
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
  })
</script>

<script>

  $(document).ready(function() {
  $('#toptask').DataTable({
      "paging":   false,
      "ordering": true,
      "searching": false,
      "info": false
    });
  });
</script>



</body>
</html>
