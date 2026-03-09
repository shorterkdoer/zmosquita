<head>
    <meta charset="UTF-8">
    <title><?php echo $_SESSION['Title']; ?></title>

    <style>
        
    .btn-gradient {
    background: linear-gradient(135deg,rgb(25, 25, 25),rgb(127, 128, 130));
    color: white;
    border: none;
  }

  .btn-gradient:hover {
    background: linear-gradient(135deg, #4338ca,rgb(173, 194, 238));
  }
  .btn-rounded {
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .btn-rounded:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  }
  
</style>

<link
  rel="stylesheet"
  href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"
/>
<link
  rel="stylesheet"
  href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"
/>


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

</head>
