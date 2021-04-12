<?php
error_reporting(E_ERROR | E_PARSE);
include("settings.php");
//Giriş & Yetki Kontrol
authority();
//
if(!is_numeric($_GET["id"]))
{
	exit();
}
//Servis Kaydı Sabit Değerleri Çek
//
$sql = oci_parse($ORACLEconnection, "SELECT A.*,B.SERIALNUMBER FROM IASMNTBREAKDOWN A INNER JOIN HAKBREAKDOWNSFORM B ON A.BREAKDOWNNUM=B.BREAKDOWNNUM WHERE A.BREAKDOWNNUM='".$_GET["id"]."' AND B.COMPANY='".$_COOKIE["company"]."'");
oci_execute($sql);
$service = oci_fetch_array($sql, OCI_ASSOC+OCI_RETURN_LOB);
//
//Servis Kaydı Daha Önce Açılmış mı?
//
if(oci_num_rows($sql)>0)
{

	$get = oci_parse($ORACLEconnection, "SELECT A.*,TO_CHAR(A.HAKARIZASONUCDETAY) AS HAKARIZASONUCDETAY_CHAR,TO_CHAR(A.HAKTAKIPDETAY) AS HAKTAKIPDETAY_CHAR FROM HAKBREAKDOWNSFORM A WHERE A.BREAKDOWNNUM='".$_GET["id"]."' AND COMPANY='".$_COOKIE["company"]."'");
	oci_execute($get, OCI_DEFAULT);
	$print = oci_fetch_array($get, OCI_ASSOC+OCI_RETURN_NULLS);

}
//Yetkilerin Ve Servis Durumunun Kontrol Edilmesi
//
if((AUTHORITY=="Staff" OR AUTHORITY=="Lab" OR AUTHORITY=="Pollster") AND ($print["ISSENTED"]=="-1" OR $print["ISSENTED"]=="1"))
{
	header("location:ssh.php");
	exit();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $service["BREAKDOWNNUM"];?> - <?php echo LANG__SERVICE__TITLE;?></title>
<link href="css/style.css" rel="stylesheet">
<link href="css/fontawesome-all.min.css" rel="stylesheet">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/jquery-ui.min.css" rel="stylesheet">
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.cookie.js"></script>
<script src="js/service.js"></script>
<script src="js/jquery.inputmask.bundle.min.js"></script>
<script src="js/readmore.min.js"></script>
<script>
$(document).ready(function() {
	//Başlangıçta Gösterilmesi Gereken Alanlar Javascript İle Gösteriliyor
	<?php
	echo $print["HAKCALDURUMSTATU"]=="1" ? '$("#caldurumdetay").show("fast");' : '$("#caldurumdetay").hide("fast");';
	//
	echo $print["HAKCALSTATU"]=="2" ? '$("#calstatudetay").show("fast");' : '$("#calstatudetay").hide("fast");';
	//
	echo $print["HAKTAKIP"]=="1" ? '$("#takipdetaynew").show("fast");' : '$("#takipdetaynew").hide("fast");';
	//
	echo $print["HAKCALSAAT"]=="1" ? '$("#calsaatsure").show("fast");' : '$("#calsaatsure").hide("fast");';
	//
	echo $print["HAKDEVREYEALMA"]=="0" ? '$("#devdetay").show("fast");' : '$("#devdetay").hide("fast");';
	//
	echo $print["HAKDOF"]=="1" ? '$("#dofdetay").show("fast");' : '$("#dofdetay").hide("fast");';

	?>

	$(document).on("click", ".popup-close", function()
	{		
		$(".popup").hide();
		$(".popup-content").empty();
		return false;
    });
	//
	$(document).on("click", ".addExpertise", function()
	{
		var callID = "<?php echo $service["BREAKDOWNNUM"];?>";
		$.post("include/add-expertise.php",{callID:callID},function(data) { $(".popup-content").append(data);$(".popup").show(); });
		return false;
    });
    //
    $(document).on("click", ".open-help", function()
	{
		var selected = $("select[name=problemSubSubType]").val();
		window.open("help.php?code="+selected, "_blank");
		return false;
    });
	//



	//Fazla karakterleri gizleme

	$('.content-more').readmore({ speed: 75, collapsedHeight: 86, lessLink: '<a href="#">Daha az göster</a>' });


	//
});
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Personelin Girdiği ve Sistemdeki Seri Numaraları Karşılaştırılıyor
////////////////////////////////////////////////////////////////////////////////////////////////////////////
function checkSerialNumber(){
	var getSerialNumber = $("#userInternalSN").val();
	var serialNumber = "<?php echo $service["SERIALNUMBER"];?>";
	if(getSerialNumber==serialNumber && getSerialNumber!="") {
		jQuery('#matching').css({display:'block'});
		jQuery('#notMatching').css({display:'none'});
	}
	else {
		jQuery('#matching').css({display:'none'});
		jQuery('#notMatching').css({display:'block'});
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
function filterForms(){
	filter=$("input[type=radio][name=forms]:checked").attr("value");
	if(filter=="dekantor") { $("#formAddDekantor").css("display", "block"); $("#formAddSeparator").css("display", "none"); }
	else { $("#formAddDekantor").css("display", "none"); $("#formAddSeparator").css("display", "block"); }
}
</script>
</head>
<body>
<!-- // Yükleniyor & Popup İşlemleri // -->
<div class="loading"></div>
<div class="popup"><div class="popup-container"><div class="popup-content"></div><a class="popup-close" href="#" ><i class="fa fa-times-circle"></i></a></div></div>
<!-- // Yükleniyor & Popup İşlemleri // -->

<div class="wrapper">
	<!-- // Sol Panel // -->
	<nav id="sidebar">
		<div class="logo-container">
			<img class="logo" src="images/logo.svg" width="95" height="40" alt="Logo" />
		</div>
		<br />
		<div class="btn btn-light"><a href="expertise.php?id=<?php echo $print["BREAKDOWNNUM"];?>"><i class="fa fa-file-text-o"></i> <?php echo LANG__SERVICE__EXPERTISEBUTTON;?></a></div>
		<br /><br />
		<?php
		$class_SQL = oci_parse($ORACLEconnection, "SELECT (CASE WHEN A.ABC=1 THEN 'A' WHEN A.ABC=2 THEN 'B' WHEN A.ABC=3 THEN 'C' WHEN A.ABC=4 THEN 'D' WHEN A.ABC=5 THEN 'E' WHEN A.ABC=6 THEN 'F' WHEN A.ABC=7 THEN 'G' ELSE '...' END) AS ABC FROM IASCUSTOMER A, IASMNTBREAKDOWN B WHERE A.CUSTOMER=B.CUSTOMER AND A.COMPANY=B.COMPANY AND B.BREAKDOWNNUM='".$print["BREAKDOWNNUM"]."'");
		oci_execute($class_SQL);
		$class = oci_fetch_array($class_SQL, OCI_ASSOC+OCI_RETURN_LOB);
		if($class["ABC"]=="A")
		{
			echo '<img src="images/a.png" height="80" />';
		}
		?>
		<br />
		<span class="fontPrimary16BoldWhite"><?php echo LANG__SERVICE__SERVICEINFO;?></span><br />
		<span class="fontPrimary16White"><?php echo LANG__SERVICE__CALLNUMBER;?>: <?php echo $print["BREAKDOWNNUM"];?></span><br />
		<span class="fontPrimary16White"><?php echo LANG__SERVICE__CUSTOMER;?>: <?php echo $service["CUSTOMER"];?></span><br />
		<span class="fontPrimary16White"><?php echo LANG__SERVICE__CUSTOMER;?>: <?php echo $service["HAKMACHINEPLACE"];?></span><br />
		<br /><br />
		<span class="fontPrimary16BoldWhite"><?php echo LANG__SERVICE__GENERALINFO;?></span><br />
		<span class="fontPrimary16White"><?php echo $service["PROBDESCRS"];?></span><br />
		<span class="fontPrimary16White"></span>
		<br /><br />
		<span class="fontPrimary16BoldWhite"><?php echo LANG__SERVICE__MACHINEINFO;?></span><br />
		<span class="fontPrimary16White"><?php echo $print["IASMATX_STEXT"];?></span>
		<br /><br />
		<span class="fontPrimary16BoldWhite"><?php echo LANG__SERVICE__ALLPHOTOVIDEOS;?></span><br />
		<?php

		$handle = opendir(dirname(realpath(__FILE__))."/app/images/".$_COOKIE["company"]."/".$print["BREAKDOWNNUM"]."/");
		while($file = readdir($handle))
		{
			if($file !== '.' && $file !== '..')
			{
			    echo '<a href="app/images/'.$_COOKIE["company"]."/".$print["BREAKDOWNNUM"]."/".$file.'" target="_blank"><img src="app/images/'.$_COOKIE["company"]."/".$print["BREAKDOWNNUM"]."/".$file.'" height="60" /></a>';
			}
		}
		?>
	</nav>
	<!-- // Sol Panel // -->
	<!-- // Sağ Panel // -->
	<div class="content">
		<?php include("include/header.php");?>
			
		<div class="container-fluid service-page-container" style="overflow-y:scroll; height: 100vh;">
			<br />
			<?php
			if(AUTHORITY=="Staff" OR AUTHORITY=="Lab")
			{

				echo '<div class="warning results col-lg-12"> '.LANG__SERVICE__WARNINGSTAFF.'</div>';
			}
			else if(AUTHORITY=="Operation")
			{

				echo '<div class="blink warning results col-lg-12"> '.LANG__SERVICE__WARNINGOPERATION.'</div>';
			}
			else
			{
				echo '<div class="blink error results col-lg-12"> '.LANG__SERVICE__WARNINGELSE.'</div>';
			}
			?>
			<div class="divider"></div>
			<div class="row">
				<div class="col-2">
					<br />
					<div id="stepOneBackButton" class="backButton" onClick="stepOneBack();" style="display:none;"><div class="backInsideButton"><i class="fa fa-undo"></i></div><?php echo LANG__SERVICE__PREVIOUSSTEP;?></div>

					<div id="stepTwoBackButton" class="backButton" onClick="stepTwoBack();" style="display:none;"><div class="backInsideButton"><i class="fa fa-undo"></i></div><?php echo LANG__SERVICE__PREVIOUSSTEP;?></div>

					<div id="stepThreeBackButton" class="backButton" onClick="stepThreeBack();" style="display:none;"><div class="backInsideButton"><i class="fa fa-undo"></i></div><?php echo LANG__SERVICE__PREVIOUSSTEP;?></div>

					<div id="stepFourBackButton" class="backButton" onClick="stepFourBack();" style="display:none;"><div class="backInsideButton"><i class="fa fa-undo"></i></div><?php echo LANG__SERVICE__PREVIOUSSTEP;?></div>
				</div>
				<div class="col-8">
					<div class="row bs-wizard" style="border-bottom:0;">

						<div class="stepOneProgress col-lg-3half bs-wizard-step complete">
							<div class="text-center bs-wizard-stepnum"><?php echo LANG__SERVICE__STEP1TITLE;?></div>
							<div class="progress"><div class="progress-bar"></div></div>
							<a href="#" class="bs-wizard-dot"></a>
							<div class="bs-wizard-info text-center"><?php echo LANG__SERVICE__STEP1SUBTITLE;?></div>
						</div>

						<div class="stepTwoProgress col-lg-3half bs-wizard-step disabled">
							<div class="text-center bs-wizard-stepnum"><?php echo LANG__SERVICE__STEP2TITLE;?></div>
							<div class="progress"><div class="progress-bar"></div></div>
							<a href="#" class="bs-wizard-dot"></a>
							<div class="bs-wizard-info text-center" style="display: none;"><?php echo LANG__SERVICE__STEP2SUBTITLE;?></div>
						</div>

						<div class="stepThreeProgress col-lg-3half bs-wizard-step disabled">
							<div class="text-center bs-wizard-stepnum"><?php echo LANG__SERVICE__STEP3TITLE;?></div>
							<div class="progress"><div class="progress-bar"></div></div>
							<a href="#" class="bs-wizard-dot"></a>
							<div class="bs-wizard-info text-center" style="display: none;"><?php echo LANG__SERVICE__STEP3SUBTITLE;?></div>
						</div>

						<div class="stepFourProgress col-lg-3half bs-wizard-step disabled">
							<div class="text-center bs-wizard-stepnum"><?php echo LANG__SERVICE__STEP4TITLE;?></div>
							<div class="progress"><div class="progress-bar"></div></div>
							<a href="#" class="bs-wizard-dot"></a>
							<div class="bs-wizard-info text-center" style="display: none;"><?php echo LANG__SERVICE__STEP4SUBTITLE;?></div>
						</div>

						<div class="stepFiveProgress col-lg-3half bs-wizard-step disabled">
							<div class="text-center bs-wizard-stepnum"><?php echo LANG__SERVICE__STEP5TITLE;?></div>
							<div class="progress"><div class="progress-bar"></div></div>
							<a href="#" class="bs-wizard-dot"></a>
							<div class="bs-wizard-info text-center" style="display: none;"><?php echo LANG__SERVICE__STEP5SUBTITLE;?></div>
						</div>
					</div>
				</div>
				<div class="col-2">
					<br />
					<div id="stepOneButton" class="saveButton" onClick="stepOne();"><?php echo LANG__SERVICE__CONTINUE;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>

					<div id="stepTwoButton" class="saveButton" onClick="stepTwo();" style="display:none;"><?php echo LANG__SERVICE__CONTINUE;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>

					<div id="stepThreeButton" class="saveButton" onClick="stepThree();" style="display:none;"><?php echo LANG__SERVICE__CONTINUE;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>

					<div id="stepFourButton" class="saveButton" onClick="stepFour();" style="display:none;"><?php echo LANG__SERVICE__CONTINUE;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>
					<?php
					if(AUTHORITY=="Staff" OR AUTHORITY=="Lab")
					{
					?>
						<div id="stepFiveButton" class="saveButton" onClick="stepFive();" style="display:none;"><?php echo LANG__SERVICE__SAVEEXIT;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>
					<?php
					}
					else if(AUTHORITY=="Planning" OR AUTHORITY=="Operation")
					{			
					?>
						<div id="stepFiveButton" class="saveButton" onClick="stepFive();" style="display:none;"><?php echo LANG__SERVICE__SAVEEXIT;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>
						<input type="hidden" id="closeCall" name="closeCall" value="<?php echo $service["BREAKDOWNNUM"];?>">
						<div id="closeCallButton" class="saveButton" onClick="closeCall();" style="display:none;"><?php echo LANG__SERVICE__CLOSECALL;?> <div class="saveInsideButton"><i class="fa fa-check-circle"></i></div></div>
					<?php
					}
					else
					{			
					?>
						<a href="ssh.php" title="Görüntülemeden Çık">
						<div id="stepFiveButton" class="saveButton" style="display:none;"><?php echo LANG__SERVICE__EXIT;?> <div class="saveInsideButton"><i class="fa fa-floppy-o"></i></div></div>
						</a>
					<?php
					}
					?>
					<br />
				</div>
			</div>
			<div class="divider"></div>
			<!-- // // -->
			<!-- Step One Start -->
			<div id="stepOneContainer">
				<form id="formStepOne" name="formStepOne" method="post" action="javascript:void(0);">
					<input type="hidden" id="breakdownnum" name="breakdownnum" value="<?php echo $service["BREAKDOWNNUM"];?>">
					<input type="hidden" id="registerId" name="registerId" value="<?php echo $_COOKIE["register"];?>">
					<input type="hidden" id="company" name="company" value="<?php echo $_COOKIE["company"];?>">
					<div class="row">
						<div class="col-8">
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__SERIALNUMBER;?></div>
								<div class="col-lg-8">
									<div class="service clearfix">
										<input type="text" id="userInternalSN" name="userInternalSN" onChange="checkSerialNumber();" placeholder="Örn: 952665189" value="<?php echo $print["SERIALNUMBER"];?>" style="float: left;">
										<div class="controlDiv">
											<i id="matching" class="fa fa-check-circle-o" style="display: none; color:#00e33a;"></i>
											<i id="notMatching" class="fa fa-ban" style="display: none; color: #c41230;"></i>
										</div>
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__WORKHOUR;?></div>
								<div class="col-lg-8">										
									<div class="service clearfix">
										<input type="radio" id="radio1" name="calsaat" value="1" <?php echo $print["HAKCALSAAT"]=="1" ? "checked" : ""; ?> onChange='$("#calsaatsure").show("fast");$("#calsaatsure").val("");'>
										<label for="radio1"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio2" name="calsaat" value="2" <?php echo $print["HAKCALSAAT"]=="2" ? "checked" : ""; ?> onChange='$("#calsaatsure").hide("fast");$("#calsaatsure").val("");'>
										<label for="radio2"><?php echo LANG__SERVICE__NO;?></label>

										<input type="number" id="calsaatsure" name="calsaatsure" value="<?php echo $print["HAKCALSAATSURE"];?>" placeholder="<?php echo LANG__SERVICE__FORINSTANCE;?>" style="display: none;">
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4"><?php echo LANG__SERVICE__HASTHEUSERINTERFEREDMALFUNCTION;?></div>
								<div class="col-lg-8">
									<div class="service clearfix">
										<input type="radio" id="radio3" name="caldurumstatu" value="1" <?php echo $print["HAKCALDURUMSTATU"]=="1" ? "checked" : ""; ?> onChange='$("#caldurumdetay").show("fast");$("#caldurumdetay").val("");'>
										<label for="radio3"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio4" name="caldurumstatu" value="2" <?php echo $print["HAKCALDURUMSTATU"]=="2" ? "checked" : ""; ?> onChange='$("#caldurumdetay").hide("fast");$("#caldurumdetay").val("");'>
										<label for="radio4"><?php echo LANG__SERVICE__NO;?></label>

										<input type="text" id="caldurumdetay" name="caldurumdetay" value="<?php echo $print["HAKCALDURUMDETAY"];?>" placeholder="Detayları giriniz..." style="display: none;">
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4"><?php echo LANG__SERVICE__HASTHEMACHINEPLACEIOK;?></div>
								<div class="col-lg-8">
									<div class="service">
										<input type="radio" id="radio5" name="calstatu" value="1" <?php echo $print["HAKCALSTATU"]=="1" ? "checked" : ""; ?> onChange='$("#calstatudetay").hide("fast");$("#calstatudetay").val("");'>
										<label for="radio5"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio6" name="calstatu" value="2" <?php echo $print["HAKCALSTATU"]=="2" ? "checked" : ""; ?> onChange='$("#calstatudetay").show("fast");$("#calstatudetay").val("");'>
										<label for="radio6"><?php echo LANG__SERVICE__NO;?></label>

										<input type="text" id="calstatudetay" name="calstatudetail" value="<?php echo $print["HAKCALSTATUDETAY"];?>" placeholder="Detayları giriniz...">
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4"><?php echo LANG__SERVICE__MAINTENANCEREASON;?></div>
								<div class="col-lg-8">
									<div class="form">
										<input type="radio" id="radio9" name="maintenancer" value="Arıza" <?php echo $print["HAKMAINTENANCEREASON"]=="Arıza" ? "checked" : ""; ?>>
										<label for="radio9"><?php echo LANG__SERVICE__MAINTENANCEREASON1;?></label>
										
										<input type="radio" id="radio7" name="maintenancer" value="Bakım" <?php echo $print["HAKMAINTENANCEREASON"]=="Bakım" ? "checked" : ""; ?>>
										<label for="radio7"><?php echo LANG__SERVICE__MAINTENANCEREASON2;?></label>

										<input type="radio" id="radio8" name="maintenancer" value="Devreye Alma" <?php echo $print["HAKMAINTENANCEREASON"]=="Devreye Alma" ? "checked" : ""; ?>>
										<label for="radio8"><?php echo LANG__SERVICE__MAINTENANCEREASON3;?></label>
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4"><?php echo LANG__SERVICE__ISSUESOURCETYPE;?></div>
								<div class="col-lg-2">
									<div class="service">
										<select class="select" name="problemType" id="problemType" style="width: 140px;">
											<?php
											if($print["HAKPROBLEMTYPE"]=="")
											{		
												echo '<option>'.LANG__SERVICE__CHOOSE.'</option>';
											}
											else
											{
												echo '<option value="'.$print["HAKPROBLEMTYPE"].'">'.$print["HAKPROBLEMTYPE"].'</option>';

											}
											$get = oci_parse($ORACLEconnection, "SELECT * FROM HAKPRDTYPES WHERE LANGU = 'T'");
											oci_execute($get);
											while($problemtype = oci_fetch_array($get, OCI_ASSOC+OCI_RETURN_NULLS))
											{

												echo '<option value="'.$problemtype["NUM"].'">'.$problemtype["PRODUCTTYPE"].'</option>';
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="service">
										<div class="subproducts">
											<?php
											if($print["HAKPROBLEMSUBTYPE"]!="")
											{		
												echo '<select class="select" id="problemSubType" name="problemSubType" style="width:210px;"><option value="'.$print["HAKPROBLEMSUBTYPE"].'">'.$print["HAKPROBLEMSUBTYPE"].'</option></select>';
											}
											?>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="service">
										<div class="hakmnt003">
											<?php
											if($print["HAKPROBLEMSUBSUBTYPE"]!="")
											{		
												echo '<select class="select" id="problemSubSubType" name="problemSubSubType" style="width:240px;"><option value="'.$print["HAKPROBLEMSUBSUBTYPE"].'">'.$print["HAKPROBLEMSUBSUBTYPE"].'</option></select>';
											}
											?>
										</div>
									</div>
								</div>
							</div>
							<br />
							<b><?php echo LANG__SERVICE__PREVIOUSBREAKDOWNS;?></b><br />
							<!-- Önceki Servis Kayıtları -->
							<table class="table" width="100%">
						    <tbody>
						    <tr>
						    <td width="0"></td>
						    <td width="130"><?php echo LANG__SERVICE__CALLNUMBER;?></td>
						    <td><?php echo LANG__SERVICE__SERIALNUMBER;?></td>
						    <td><?php echo LANG__SERVICE__MACHINEPLACE;?></td>
						    <td><?php echo LANG__SERVICE__SERVICEINFO;?></td>
						    <td><?php echo LANG__SSH__SERVICETYPE;?></td>
						    <td width="150"><?php echo LANG__SSH__DATE;?></td>
						    </tr>
							<?php
							$prevRecordsSql = oci_parse($ORACLEconnection, "SELECT A.BREAKDOWNNUM, A.BREAKDOWNTYPE, A.STATUS, A.COMPANY, A.ISDELETED, A.HAKMACHINEPLACE, A.PROBDESCRS, A.CREATEDAT, A.CREATEDBY, A.CUSTOMER, B.ISSENTED, B.SERIALNUMBER FROM ıasmntbreakdown A LEFT JOIN hakbreakdownsform B ON A.BREAKDOWNNUM = B.BREAKDOWNNUM AND A.COMPANY = B.COMPANY WHERE A.COMPANY = '".$_COOKIE["company"]."' AND A.GRCCUSTOMER='".$service["GRCCUSTOMER"]."' AND A.BREAKDOWNNUM  NOT LIKE '14%' AND A.ISDELETED = 0 AND A.GROUPCODE IN ('01','03','04') AND B.SERIALNUMBER='".$print["SERIALNUMBER"]."' ORDER BY A.BREAKDOWNNUM DESC OFFSET 0 ROWS FETCH NEXT 20 ROWS ONLY");
							oci_execute($prevRecordsSql); // AND A.STATUS IN (0,1)
							while($prevRecords = oci_fetch_array($prevRecordsSql, OCI_ASSOC+OCI_RETURN_NULLS))
							{
								echo '<tr>
						        <td width="0"></td>
						        <td>'.$prevRecords["BREAKDOWNNUM"].'</td>
						        <td>'.$prevRecords["SERIALNUMBER"].'</td>
						        <td>'.$prevRecords["HAKMACHINEPLACE"].'</td>
						        <td>'.$prevRecords["PROBDESCRS"].'</td>
						        <td>'.$prevRecords["BREAKDOWNTYPE"].'</td>
						        <td>'.$prevRecords["CREATEDAT"].'</td>
						        </tr>';
							}
							?>
							</tbody></table>
							<!-- Önceki Servis Kayıtları -->
						</div>
						<div class="col-4">
							<div class="service">
								<?php echo LANG__SERVICE__DETAILOFPROCESS;?><br />
								<textarea class="textbox" id="tespitnew" name="tespitnew"><?php echo $print["HAKTESPIT"];?></textarea>
								<br />
								<?php
								$timeline1_SQL = oci_parse($ORACLEconnection, "SELECT A.*, TO_CHAR(CONTENT) AS CONTENT_CHAR FROM HAKASAPTIMELINE A WHERE A.BREAKDOWNNUM = '".$_GET["id"]."' AND A.COMPANY = '".$_COOKIE["company"]."' AND A.TYPE='1' ORDER BY CREATEDAT DESC");
								oci_execute($timeline1_SQL);
								while($timeline1 = oci_fetch_array($timeline1_SQL, OCI_ASSOC+OCI_RETURN_NULLS))
								{
									/* FIND STAFF */
									$empfindsql = oci_parse($ORACLEconnection, "SELECT PERSONNELNAME FROM HAKPERSONNELSFORM WHERE PERSONNELNUM='".$timeline1["REGISTERID"]."'");
									oci_execute($empfindsql);
									$empfind = oci_fetch_array($empfindsql, OCI_ASSOC+OCI_RETURN_NULLS);
									/* FIND STAFF*/
									echo '
									<div class="media">
										<span class="name-box">'.avatar($empfind["PERSONNELNAME"]).'</span>
										<div class="media-body">
											<span style="font-size:14px; font-weight:700;">'.$timeline1["REGISTERID"].' · '.$empfind["PERSONNELNAME"].' · '.when($timeline1["CREATEDAT"]).'</span>
											<div class="content-more" style="font-size:14px; overflow:hidden;">'.$timeline1["CONTENT_CHAR"].'</div>
										</div>
									</div>';
								}
								?>

								<br />
								<?php echo LANG__SERVICE__OBSERVEDISSUESOURCE;?><br />
								<textarea class="textbox" id="arizanedentext" name="arizanedentext"></textarea>
								<br />
								<?php
								$timeline2_SQL = oci_parse($ORACLEconnection, "SELECT A.*, TO_CHAR(CONTENT) AS CONTENT_CHAR FROM HAKASAPTIMELINE A WHERE A.BREAKDOWNNUM = '".$_GET["id"]."' AND A.COMPANY = '".$_COOKIE["company"]."' AND A.TYPE='2' ORDER BY CREATEDAT DESC");
								oci_execute($timeline2_SQL);
								while($timeline2 = oci_fetch_array($timeline2_SQL, OCI_ASSOC+OCI_RETURN_NULLS))
								{
									/* FIND STAFF */
									$empfindsql = oci_parse($ORACLEconnection, "SELECT PERSONNELNAME FROM HAKPERSONNELSFORM WHERE PERSONNELNUM='".$timeline2["REGISTERID"]."'");
									oci_execute($empfindsql);
									$empfind = oci_fetch_array($empfindsql, OCI_ASSOC+OCI_RETURN_NULLS);
									/* FIND STAFF*/
									echo '
									<div class="media">
										<span class="name-box">'.avatar($empfind["PERSONNELNAME"]).'</span>
										<div class="media-body">
											<span style="font-size:14px; font-weight:700;">'.$timeline2["REGISTERID"].' · '.$empfind["PERSONNELNAME"].' · '.when($timeline2["CREATEDAT"]).'</span>
											<div class="content-more" style="font-size:14px; overflow:hidden;">'.$timeline2["CONTENT_CHAR"].'</div>
										</div>
									</div>';
								}
								?>
							</div>
						</div>
					</div>
				</form>

			</div>
			<!-- Step One End -->
			<!-- // // -->
			<!-- Step Two Start -->
			<div id="stepTwoContainer" style="display: none;">
				<br />
				<div class="blink error col-lg-12"> LÜTFEN KİŞİ BİLGİLERİNİ EKSİKSİZ VE DOĞRU GİRİNİZ!</div>
				<form id="formStepTwo" name="formStepTwo" method="post" action="javascript:void(0);">
					<input type="hidden" id="breakdownnum" name="breakdownnum" value="<?php echo $service["BREAKDOWNNUM"];?>">
					<input type="hidden" id="customer" name="customer" value="<?php echo $service["CUSTOMER"];?>">
					<input type="hidden" id="registerId" name="registerId" value="<?php echo $_COOKIE["register"];?>">
					<input type="hidden" id="company" name="company" value="<?php echo $_COOKIE["company"];?>">
					<div class="row">
						<div class="col-10">							

							<span class="pull-right"><a href="#" class="addCustomerContact"><i class="fa fa-plus-circle fa-2x"></i></a></span>
							<br />
							<div class="service customerContact">
								<?php
									$getcrm_SQL = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPCRM WHERE BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
									oci_execute($getcrm_SQL);

									while($crm = oci_fetch_array($getcrm_SQL, OCI_ASSOC+OCI_RETURN_NULLS))
									{
										echo '<div class="field"><select class="select" id="customerPosition" name="customerPosition[]">';
										echo '<option value="'.$crm["POSITION"].'">'.$crm["POSITION"].'</option>';
										echo '<option value="'.LANG__SERVICE__OWNER.'">'.LANG__SERVICE__OWNER.'</option>
										<option value="'.LANG__SERVICE__MAINTAINER.'">'.LANG__SERVICE__MAINTAINER.'</option>
										<option value="'.LANG__SERVICE__PURCHASEMANAGER.'">'.LANG__SERVICE__PURCHASEMANAGER.'</option>
										<option value="'.LANG__SERVICE__OP.'">'.LANG__SERVICE__OP.'</option>
										<option value="'.LANG__SERVICE__FACTORYMANAGER.'">'.LANG__SERVICE__FACTORYMANAGER.'</option>
										<option value="'.LANG__SERVICE__OTHER.'">'.LANG__SERVICE__OTHER.'</option>
										</select> ';
										echo '<input type="text" id="customerFullName" name="customerFullName[]" value="'.$crm["FULLNAME"].'"> ';
										echo '<input class="phoneMask" type="text" id="customerPhone" name="customerPhone[]" value="'.$crm["PHONE"].'"> ';
										echo '<input class="emailMask" type="text" id="customerEmail" name="customerEmail[]" value="'.$crm["EMAIL"].'"> ';
										echo '<select class="select" id="customerSector" name="customerSector[]">';
										echo '<option value="'.$crm["SECTOR"].'">'.$crm["SECTOR"].'</option>';
										echo '<option value="'.LANG__SERVICE__OLIVEOIL.'">'.LANG__SERVICE__OLIVEOIL.'</option>
										<option value="'.LANG__SERVICE__INDUSTRY.'">'.LANG__SERVICE__INDUSTRY.'</option>
										<option value="'.LANG__SERVICE__ENVIRONMENT.'">'.LANG__SERVICE__ENVIRONMENT.'</option>
										<option value="'.LANG__SERVICE__ENERGY.'">'.LANG__SERVICE__ENERGY.'</option>
										<option value="'.LANG__SERVICE__MILK.'">'.LANG__SERVICE__MILK.'</option>
										<option value="'.LANG__SERVICE__FOOD.'">'.LANG__SERVICE__FOOD.'</option>
										</select><a href="#" class="removeCustomerContact"><i class="fa fa-trash"></i></a></div>';
									}
								?>
							</div>
						</div>
					</div>
				</form>
			</div>
			<!-- Step Two End -->
			<!-- // // -->
			<!-- Step Three Start -->
			<div id="stepThreeContainer" style="display: none;">
				<form id="formStepThree" name="formStepThree" method="post" action="javascript:void(0);">
					<input type="hidden" id="breakdownnum" name="breakdownnum" value="<?php echo $service["BREAKDOWNNUM"];?>">
					<input type="hidden" id="registerId" name="registerId" value="<?php echo $_COOKIE["register"];?>">
					<input type="hidden" id="company" name="company" value="<?php echo $_COOKIE["company"];?>">
					<div class="row">
						<div class="col-6">							
							<?php echo LANG__SERVICE__INSTALLEDPARTS;?>
							<div class="service addedMaterials addedField">
							<?php
							if(!empty($print["U_HAKBAYI"]))
							{
								echo '<span class="pull-right"><a href="#" class="addedDealerMaterialsButton"><i class="fa fa-plus-circle fa-2x"></i></a></span>';
							}
							else
							{
								echo '<span class="pull-right"><a href="#" class="addedMaterialsButton"><i class="fa fa-plus-circle fa-2x"></i></a></span>';
							}
							?>
							<br />
							<?php

							$check = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPMATERIALS WHERE STATUS='Added' AND BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
							oci_execute($check);
							oci_fetch_array($check, OCI_ASSOC+OCI_RETURN_NULLS);
							if(oci_num_rows($check)>0)
							{
								$sql = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPMATERIALS WHERE STATUS='Added' AND BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
								oci_execute($sql);
								while($materials = oci_fetch_array($sql, OCI_ASSOC+OCI_RETURN_NULLS))
								{
									echo '
									<div class="field">
									<input class="addedMaterialCode auto" type="text" id="addedMaterialCode" name="addedMaterialCode[]" value="'.$materials["CODE"].'"> ';
									echo '<input type="number" id="addedMaterialQuantity" name="addedMaterialQuantity[]"  value="'.$materials["QUANTITY"].'"> ';
									echo '
									<input type="text" id="addedMaterialDesc" name="addedMaterialDesc[]" value="'.$materials["DESCRIPTION"].'" readonly>
									<a href="#" class="removeField"><i class="fa fa-trash"></i></a>
									</div>';
								}
							}
							?>
							</div>
						</div>
						<div class="col-6">							
							<?php echo LANG__SERVICE__REMOVEDPARTS;?>
							<div class="service removedMaterials removedField">
							<span class="pull-right"><a href="#" class="removedMaterialsButton"><i class="fa fa-plus-circle fa-2x"></i></a></span>
							<br />
							<?php
							$check = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPMATERIALS WHERE STATUS='Removed' AND BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
							oci_execute($check);
							oci_fetch_array($check, OCI_ASSOC+OCI_RETURN_NULLS);
							if(oci_num_rows($check)>0)
							{
								$sql = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPMATERIALS WHERE STATUS='Removed' AND BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
								oci_execute($sql);
								while($materials = oci_fetch_array($sql, OCI_ASSOC+OCI_RETURN_NULLS))
								{
									echo '
									<div class="field">
									<input class="removedMaterialCode auto" type="text" id="removedMaterialCode" name="removedMaterialCode[]" value="'.$materials["CODE"].'">
									<input type="number" id="removedMaterialQuantity" name="removedMaterialQuantity[]"  value="'.$materials["QUANTITY"].'">
									<input type="text" id="removedMaterialDesc" name="removedMaterialDesc[]" value="'.$materials["DESCRIPTION"].'" readonly>
									<a href="#" class="removeField"><i class="fa fa-trash"></i></a>
									</div>';
								}
							}
							?>
							</div>
						</div>
					</div>
				</form>
			</div>
			<!-- Step Three End -->
			<!-- // // -->
			<!-- Step Four Start -->
			<div id="stepFourContainer" style="display: none;">
				<form id="formStepFour" name="formStepFour" method="post" action="javascript:void(0);">
					<input type="hidden" id="breakdownnum" name="breakdownnum" value="<?php echo $service["BREAKDOWNNUM"];?>">
					<input type="hidden" id="registerId" name="registerId" value="<?php echo $_COOKIE["register"];?>">
					<input type="hidden" id="company" name="company" value="<?php echo $_COOKIE["company"];?>">
					<input type="hidden" id="sernum" name="sernum" value="<?php echo $service["SERIALNUMBER"];?>">
					<div class="row">
						<div class="col-6">						
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__HASTHEREQUESTENDED;?></div>
								<div class="col-lg-8">
									<div class="service">			
										<input type="radio" id="radio10" name="arizasonuc" value="1" <?php echo $print["HAKARIZASONUC"]=="1" ? "checked" : ""; ?>>
										<label for="radio10"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio11" name="arizasonuc" value="2" <?php echo $print["HAKARIZASONUC"]=="2" ? "checked" : ""; ?>>
										<label for="radio11"><?php echo LANG__SERVICE__NO;?></label>

										<textarea class="textbox" id="arizasonucdetay" name="arizasonucdetay" placeholder="<?php echo LANG__SERVICE__FILLTHEFIELD;?>"><?php echo $print["HAKARIZASONUCDETAY_CHAR"];?></textarea>
									</div>
								</div>
							</div>
							<div class="divider"></div>
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__HAUSFOLLOW;?></div>
								<div class="col-lg-8">
									<div class="service">

										<input type="radio" id="radio12" name="takip" value="1" <?php echo $print["HAKTAKIP"]=="1" ? "checked" : ""; ?> onChange='$("#takipdetaynew").show("fast");$("#takipdetaynew").val("");'>
										<label for="radio12"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio13" name="takip" value="2" <?php echo $print["HAKTAKIP"]=="2" ? "checked" : ""; ?> onChange='$("#takipdetaynew").hide("fast");$("#takipdetaynew").val("");'>
										<label for="radio13"><?php echo LANG__SERVICE__NO;?></label>

										<textarea class="textbox" id="takipdetaynew" name="takipdetaynew" placeholder="<?php echo LANG__SERVICE__FILLTHEFIELD;?>" style="display: none;"><?php echo $print["HAKTAKIPDETAY_CHAR"];?></textarea>

									</div>
								</div>
							</div>
						</div>
						<div class="col-6">					
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__CREATEMACHINETESTRECORD;?></div>
								<div class="col-lg-8">
									<div class="service">
										<input type="radio" id="radio16" name="dev" value="1" <?php echo $print["HAKDEVREYEALMA"]=="1" ? "checked" : ""; ?> onChange='$("#devdetay").hide("fast");$("#devdetay").val("");'>
										<label for="radio16"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio17" name="dev" value="0" <?php echo $print["HAKDEVREYEALMA"]=="0" ? "checked" : ""; ?> onChange='$("#devdetay").show("fast");$("#devdetay").val("");'>
										<label for="radio17"><?php echo LANG__SERVICE__NO;?></label>

										<textarea class="textbox" id="devdetay" name="devdetay" placeholder="<?php echo LANG__SERVICE__FILLTHEFIELD;?>" style="display: none;"><?php echo $print["HAKDEVREYEALMADETAY"];?></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4" style="margin-top: 8px;"><?php echo LANG__SERVICE__WANNACREATEACAP;?></div>
								<div class="col-lg-8">
									<div class="service">
										<input type="radio" id="radio18" name="hakdof" value="1" <?php echo $print["HAKDOF"]=="1" ? "checked" : ""; ?> onChange='$("#dofdetay").show("fast");$("#dofdetay").val("");'>
										<label for="radio18"><?php echo LANG__SERVICE__YES;?></label>

										<input type="radio" id="radio19" name="hakdof" value="2" <?php echo $print["HAKDOF"]=="2" ? "checked" : ""; ?> onChange='$("#dofdetay").hide("fast");$("#dofdetay").val("");'>
										<label for="radio19"><?php echo LANG__SERVICE__NO;?></label>

										<textarea class="textbox" id="dofdetay" name="dofdetay" placeholder="<?php echo LANG__SERVICE__FILLTHEFIELD;?>" style="display: none;"><?php echo $print["HAKDOFDETAY"];?></textarea>
									</div>
								</div>
							</div>



							<a href="http://lab.hauscloud.net/lab.php?breakdownnum=<?php echo $service["BREAKDOWNNUM"];?>" target="_blank">
								<span style="font-size: 18px; font-weight: bold;">HAUS LAB PORTAL</span>
								<img src="images/asaplab.png" height="100" />
								<br />
								<span style="font-size: 18px; font-weight: bold;"><?php echo LANG__SERVICE__LABPORTALDESC;?></span>
							</a>
						</div>								
					</div>
				</form>
			</div>
			<!-- Step Four End -->
			<!-- // // -->
			<!-- Step Five Start -->
			<div id="stepFiveContainer" style="display: none;">
				<form id="formStepFive" name="formStepFive" method="post" action="javascript:void(0);">
					<input type="hidden" id="breakdownnum" name="breakdownnum" value="<?php echo $service["BREAKDOWNNUM"];?>">
					<input type="hidden" id="registerId" name="registerId" value="<?php echo $_COOKIE["register"];?>">
					<input type="hidden" id="company" name="company" value="<?php echo $_COOKIE["company"];?>">
					<div class="row">
						<div class="col-lg-12">
							Günlük Limitleriniz<br />
							<?php
							$userSQL = oci_parse($ORACLEconnection, "SELECT * FROM HAKPERSONNELSFORM WHERE PERSONNELNUM='".$_COOKIE["register"]."'");
							oci_execute($userSQL, OCI_DEFAULT);
							$user = oci_fetch_array($userSQL, OCI_ASSOC+OCI_RETURN_NULLS);
							?>
							Yemek Harcama Limiti: <?php echo $user["FOODBALANCE"];?><br />
							Otel Harcama Limiti: <?php echo $user["HOTELBALANCE"];?>
							<br />
							<span class="pull-right"><a href="#" class="addExpensesButton"><i class="fa fa-plus-circle fa-2x"></i></a></span>
							<br />
							<div class="service expenses" style="height:calc(100vh - 200px); overflow: scroll;">
								<?php
								$expenses_SQL = oci_parse($ORACLEconnection, "SELECT * FROM HAKASAPEXPENSES WHERE BREAKDOWNNUM='".$service["BREAKDOWNNUM"]."' AND COMPANY='".$_COOKIE["company"]."'");
								oci_execute($expenses_SQL);
								while($expenses = oci_fetch_array($expenses_SQL, OCI_ASSOC+OCI_RETURN_NULLS))
								{
									echo '<div class="field"><select class="select" name="harcamaTipi[]" id="harcamaTipi">
										<option value='.$expenses["HARCAMATIPI"].'>'.$expenses["HARCAMATIPI"].'</option>
										<option value="'.LANG__SERVICE__ACCOMMODATION.'">'.LANG__SERVICE__ACCOMMODATION.'</option>
										<option value="'.LANG__SERVICE__TRANSPORTATION.'">'.LANG__SERVICE__TRANSPORTATION.'</option>
										<option value="'.LANG__SERVICE__FOOD.'">'.LANG__SERVICE__FOOD.'</option>
										<option value="'.LANG__SERVICE__MATERIALSANDLABOR.'">'.LANG__SERVICE__MATERIALSANDLABOR.'</option>
										<option value="'.LANG__SERVICE__CARGO.'">'.LANG__SERVICE__CARGO.'</option>
										<option value="'.LANG__SERVICE__OTHER.'">'.LANG__SERVICE__OTHER.'</option>
									</select>
									<input type="text" id="belgeSeriNo" name="belgeSeriNo[]" value="'.$expenses["BELGESERINO"].'">
									<input type="text" id="belgeCari" name="belgeCari[]" value="'.$expenses["CARIADI"].'">
									<input type="text" id="description" name="description[]" value="'.$expenses["DESCRIPTION"].'">
									<input type="text" class="price" id="tutar" name="tutar[]" value="'.$expenses["TUTAR"].'">
									<select class="select" name="currency[]" id="currency">
									<option>'.$expenses["CURRENCY"].'</option>
									<option value="TL">TL ₺</option>
									<option value="EURO">EURO €</option>
									<option value="DOLAR">DOLAR $</option>
									<option value="RUPEE">RUPEE ₹</option>
									</select>
									<select class="select" name="odemeTipi[]" id="odemeTipi">
										<option value='.$expenses["ODEMETIPI"].'>'.$expenses["ODEMETIPI"].'</option>
										<option value="'.LANG__SERVICE__CASH.'">'.LANG__SERVICE__CASH.'</option>
										<option value="'.LANG__SERVICE__CREDITCARD.'">'.LANG__SERVICE__CREDITCARD.'</option>
									</select> <a href="#" class="removeField"><i class="fa fa-trash"></i></a></div>';
								}
								?>
							</div>
						</div>							
					</div>
				</form>
			</div>
			<!-- Step Five End -->
		</div>
	</div>
	<!-- // Sağ Panel // -->
</div>
	
	
	
	




</body>
</html>
