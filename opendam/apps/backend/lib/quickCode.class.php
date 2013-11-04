<?php
include sfConfig::get("sf_lib_dir")."/qrcode/qrlib.php";

	class QuickCode {
		private $content = null;
		private $filename = null;
		private $errorCorrectionLevel = null;
		private $matrixPointSize = null;

		public function __construct() {
			$this->content = 0;
			$this->filename = 0;
			$this->errorCorrectionLevel = 0;
			$this->matrixPointSize = 0;
		}

		public function setContent($content)
		{
			$this->content = $content;
		}

		public function getContent()
		{
			return $this->content;
		}

		public function setFilename($filename)
		{
			$this->filename = $filename;
		}

		public function getFilename()
		{
			return $this->filename;
		}

		public function setErrorCorrectionLevel($errorCorrectionLevel)
		{
			$allowed = array("L", "M", "Q", "H");
			if(!in_array($errorCorrectionLevel, $allowed))
				throw new Exception("QrCode::errorCorrectionLevel is not allowed (".$errorCorrectionLevel.")");

			$this->errorCorrectionLevel = $errorCorrectionLevel;
		}

		public function getErrorCorrectionLevel()
		{
			return $this->errorCorrectionLevel;
		}

		public function setMatrixPointSize($matrixPointSize)
		{
			$allowed = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
			if(!in_array($matrixPointSize, $allowed))
				throw new Exception("QrCode::matrixPointSize is not allowed (".$matrixPointSize.")");

			$this->matrixPointSize = $matrixPointSize;
		}

		public function getMatrixPointSize()
		{
			return $this->matrixPointSize;
		}

		public function getCode()
		{
			if(!sizeof($this->getContent()))
				throw new Exception("QrCode::content not defined");

			if(!sizeof($this->getFilename()))
				throw new Exception("QrCode::filename not defined");

			if(!sizeof($this->getErrorCorrectionLevel()))
				throw new Exception("QrCode::errorCorrectionLevel not defined");

			if(!sizeof($this->getMatrixPointSize()))
				throw new Exception("QrCode::matrixPointSize not defined");

			return QRcode::png($this->content, $this->filename, $this->errorCorrectionLevel, $this->matrixPointSize);
		}
	}
?>