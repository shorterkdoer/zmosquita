<head>
    <meta charset="UTF-8">
    <title><?php echo App\Core\Session::get('Title', ''); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
   <!-- <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js"></script>

    
    <link href=" https://cdn.jsdelivr.net/npm/bootswatch/dist/morph/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- CSS de DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />


<link rel="stylesheet" type="text/css" href="/iconfont/icofont.min.css">
<!-- JS de DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Galería de Lightbox2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js" defer></script>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
  // Set worker path for PDF.js
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>

  






		<style type="text/css">
body > .container, 
body > .container-fluid {
    width: 85% !important;
    max-width: 85% !important;
    margin: 0 auto;
}
			body {
				margin: 0;
				padding: 0;
				background:rgb(224, 217, 222);
			}
			.header {
				border-bottom: 1px solid #6d247f;
				padding: 10px 0;
				margin-bottom: 10px;
			}
			.container {
				width: 95%;
				margin: 0 auto;
			}
			.ico-title {
				font-size: 2em;
			}
			.iconlist {
				margin: 0;
				padding: 0;
				list-style: none;
				text-align: center;
				width: 100%;
				display: flex;
				flex-wrap: wrap;
				flex-direction: row;
			}
			.iconlist li {
				position: relative;
				margin: 5px;
				width: 150px;
				cursor: pointer;
			}
			.iconlist li .icon-holder {
				position: relative;
				text-align: center;
				border-radius: 3px;
				overflow: hidden;
				padding-bottom: 5px;
				background: #ffffff;
				border: 1px solid #E4E5EA;
				transition: all 0.2s linear 0s;
			}
			.iconlist li .icon-holder:hover {
				background: #6d247f;
				color: #ffffff;
			}
			.iconlist li .icon-holder:hover .icon i {
				color: #ffffff;
			}
			.iconlist li .icon-holder .icon {
				padding: 20px;
				text-align: center;
			}
			.iconlist li .icon-holder .icon i {
				font-size: 3em;
				color: #1F1142;
			}
			.iconlist li .icon-holder span {
				font-size: 14px;
				display: block;
				margin-top: 5px;
				border-radius: 3px;
			}
		</style>

<style>
.btn-outline-primary-cust {
    background-color: #ac3587;
    color: #ffffff;
    border-color: #e67e22;
}

.btn-outline-primary-cust:hover {
    background-color: #6d247f;
    color: #fff;
    border-color: #6d247f;
}
</style>
 
</head>
