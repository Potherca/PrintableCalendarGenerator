<?php
/**
 *
 */
class Calendar extends Image
{

////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var CalendarDimensions
     */
    protected $m_oDimensions;

    /**
     * @var \DateInterval
     */
    protected $m_oOneDay;

    /**
     * @var
     */
    protected $m_aColors;

    /**
     * @var bool
     */
    protected $m_bDebug=false;

    /**
     * @var array DayBlockDimensions
     */
    protected $m_aDateCoordinates = array();

    /**
     * @var array
     *
     * @TODO: Replace $m_aDecorations array with DecorationCollection object
     */
    protected $m_aDecorations = array();

    /**
     * @var array
     */
    protected $m_aAppliedDecorations = array();

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_aColors
     */
    public function setColors($p_aColors)
    {
        $this->m_aColors = $p_aColors;
    }

    /**
     * @return array
     */
    public function getColors()
    {
        return $this->m_aColors;
    }

    /**
     * @return array
     */
    public function getDateCoordinates()
    {
        return $this->m_aDateCoordinates;
    }

    /**
     * @return array
     */
    public function getDecorations()
    {
        return $this->m_aDecorations;
    }

    /**
     * @TODO: Replace $p_aDecorations array with DecorationCollection object
     * @param array $p_aDecorations
     */
    public function setDecorations(/*DecorationCollection */$p_aDecorations)
    {
        $this->m_aDecorations = $p_aDecorations;
    }
// 10 px = $this->getWidth()/175.4
//  8 px = $this->getWidth()/219.25
//  6 px = $this->getWidth()/292.33333333333
//  4 px = $this->getWidth()/438.5;
//  2 px = $this->getWidth()/877;
////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param Dimensions $p_oDimensions
     * @param string $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(Dimensions $p_oDimensions, $p_sType='png', $p_bAlpha=true)
    {
        parent::__construct($p_oDimensions, $p_sType, $p_bAlpha);

        $this->m_oOneDay = new DateInterval('P1D');
    }

    public function create()
    {
        parent::create();

        $this->m_sFontDirectory = './fonts';
        $this->m_sFontPath      = '/erasblkb.pfb';

        DayBlockDimensions::setDimensionsFromParent($this->m_oDimensions);

        $this->buildColors();
    }

    /**
     * @param array $p_sBackgroundColor
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function buildColors($p_sBackgroundColor=array('0xFF', '0xFF','0xFF'))
    {

        if(!isset($this->m_rImage))
        {
            throw new Exception('Cannot allocate colors, Image has not yet been created. Please invoke the "create" or "loadFromFile" method before trying to build colors.');
        }
        else
        {
            // set background color first
            $this->m_aColors = array(
                'background' => imagecolorallocate($this->m_rImage, $p_sBackgroundColor[0], $p_sBackgroundColor[1], $p_sBackgroundColor[2]),
            );

            // set common colors
            $this->m_aColors['white'] = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0xFF);
            $this->m_aColors['black'] = imagecolorallocate($this->m_rImage, 0x00, 0x00,0x00);

            $this->m_aColors['red']   = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0x00);
            $this->m_aColors['blue']  = imagecolorallocate($this->m_rImage, 0x00, 0x00,0xFF);
            $this->m_aColors['green'] = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0x00);

            $this->m_aColors['magenta'] = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0xFF);
            $this->m_aColors['cyan']    = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0xFF);
            $this->m_aColors['yellow']  = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0x00);

            $this->m_aColors['Weekend'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBF, 0xBF);
            $this->m_aColors['Holiday'] = imagecolorallocate($this->m_rImage, 0xAA, 0xAB, 0xAA);

            $this->m_aColors['Week_Nr']         = imagecolorallocate($this->m_rImage, 0xCD, 0xCD, 0xCC);
            $this->m_aColors['Week_Nr_Border']  = imagecolorallocate($this->m_rImage, 0x66, 0x66, 0x66);
            $this->m_aColors['Week_Nr_Divider'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBC, 0xBC);

            $this->m_aColors[DecorationType::BIRTHDAY]         = imagecolorallocatealpha($this->m_rImage, 0xFF, 0xFF, 0x00, 64);
            #$this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = imagecolorallocatealpha($this->m_rImage, 0x00, 0xFF, 0xFF, 64);
            $this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = imagecolorallocatealpha($this->m_rImage, 0x99, 0x9A, 0x99, 64);
            $this->m_aColors[DecorationType::SCHOOL_HOLIDAY]   = imagecolorallocate($this->m_rImage, 0x99, 0x9A, 0x99);//, 0xFF, 0x00, 0xFF, 64);
            $this->m_aColors[DecorationType::SECULAR_HOLIDAY]  = imagecolorallocatealpha($this->m_rImage, 0x00, 0x00, 0xFF, 64);

            return $this->m_aColors;
        }
    }

    /**
     * @param DateTime $p_oDate
     *
     * @return null|string
     */
    public function render(DateTime $p_oDate)
    {
        // Create an Image
        $this->create();

        $this->calculateDateCoordinates($p_oDate);

        //@TODO: implements methods needed by $this->drawBase();
        //$this->drawBase();

        $this->drawDecorationBackgrounds();

        $this->writeMonth($p_oDate);

        $this->writeWeekNumbers($p_oDate);

        $this->writeDayNumbers($p_oDate);

        $this->drawDecorations();

        return $this->output();
    }


//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     *
     * @return DateTime
     */
    protected static function buildDateForDayNumbers(DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oDate->sub(new DateInterval('P' . $oDate->format('w') . 'D'));

        if ($oDate->format('M') === $p_oDate->format('M')) {
            $oDate->sub(new DateInterval('P1W'));
        }#if

        return $oDate;
    }

    /**
     * @param DateTime $oDate
     * @param $p_iColumnCounter
     * @param $p_iRowCounter
     * @param $p_iX
     * @param $p_iY
     */
    protected function storeDateCoordinates(DateTime $oDate
        , $p_iColumnCounter, $p_iRowCounter, $p_iX, $p_iY)
    {
        $oDimensions = DayBlockDimensions::createFromParentDimensions();

        $oDimensions->setColumn($p_iColumnCounter);
        $oDimensions->setRow($p_iRowCounter);

        $oDimensions->setX($p_iX);
        $oDimensions->setY($p_iY);

        $this->m_aDateCoordinates[$oDate->format('Ymd')] = $oDimensions;
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDecoration(Decoration $p_oDecoration)
    {
        return $this->getDimensionsForDate($p_oDecoration->getStartDate());
    }

    /**
     * @param \DateTime $p_oDate
     *
     * @throws OutOfRangeException
     *
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDate(DateTime $p_oDate)
    {
        //@FIXME: If the given date is out of scope the first or last dateCoordinate should be returned, respectively.
        $oDimensions = null;

        $aDateCoordinates = $this->getDateCoordinates();
        $sDate = $p_oDate->format('Ymd');

        if (!isset($aDateCoordinates[$sDate]))
        {
            $aDates = array_keys($aDateCoordinates);

            $sFirst = array_shift($aDates);
            $sLast = array_pop($aDates);

            if($sDate < $sFirst)
            {
                $oDimensions = $aDateCoordinates[$sFirst];
            }
            elseif($sDate > $sLast)
            {
                $oDimensions = $aDateCoordinates[$sLast];
            }
            else
            {
                throw new \OutOfRangeException('Given date is out of range and a min or max replacement could not be found.');
            }
        }
        else
        {
            /** @var $oDimensions DayBlockDimensions */
            $oDimensions = $aDateCoordinates[$sDate];
        }

        return $oDimensions;
    }

/////////////////////////////// Writing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     */
    protected  function writeDayNumbers(DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $this->m_iFontSize = $this->getWidth()/29.2333333333334;

        $iLetterBorder = $this->getWidth()/877;
        $iPadding    = $this->getWidth()/219.25;

        foreach($this->m_aDateCoordinates as $oDateCoordinate)
        {
            /** @var $oDateCoordinate DayBlockDimensions */
            $oDate->add($this->m_oOneDay);

            $iX = $oDateCoordinate->getX();
            $iY = $oDateCoordinate->getY();

            $sDay = $oDate->format('j');

            $oBoundingBox = $this->getBoundingBoxForText($sDay);

            $this->debug(null, $iX, $iY, $oDate);

            if($oDate->format('M') === $p_oDate->format('M'))
            {
                $this->writeText(
                      $sDay
                    , $iX+$iPadding, $iY+$iPadding+$oBoundingBox->getHeight()
                    , $this->m_aColors['black']
                );
            }
            else
            {
                $this->writeTextWithBorder(
                      $sDay
                    , $iX+$iPadding, $iY+$iPadding+$oBoundingBox->getHeight()
                    , $this->m_aColors['white']
                    , $iLetterBorder
                    , $this->m_aColors['black']
                );
            }#if
        }#foreach
    }

    /**
     * @param DateTime $p_oDate
     */
    protected function writeMonth(DateTime $p_oDate)
    {
        $this->m_iFontSize = $this->getWidth()/13.492307692308;

        $sMonth = $p_oDate->format('F - Y');

        $oBoundingBox = $this->getBoundingBoxForText($sMonth);

        $iX = ($this->getWidth() - $oBoundingBox->getWidth())/2;
        $iY = ($this->getHeight()/10.80) - $this->m_iFontSize/2.5;

        $this->debug();

        $this->writeText($sMonth, $iX, $iY, $this->m_aColors['black']);
    }

    /**
     * @param DateTime $p_oDate
     */
    protected  function writeWeekNumbers(DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oOneWeek = new DateInterval('P7D');

        $iOffsetTop  = $this->getHeight()/7.1556195965418;
        $iOffsetLeft = $this->getWidth()/46.157894736842;

        $iWidth  = DayBlockDimensions::getBlockWidth()/2;
        $iHeight = DayBlockDimensions::getBlockHeight() + DayBlockDimensions::getLineHeight();

        $iBorderSize = $this->getWidth()/877;

        $this->m_iFontSize = $this->getWidth()/29.2333333333334;

        for($iCounter = 0; $iCounter < 6; $iCounter++)
        {
            $sWeek = $oDate->format('W');

            $iY = $iOffsetTop + ($iHeight * $iCounter);
            $this->debug(null, $iOffsetLeft, $iY);

            $oBoundingBox = $this->getBoundingBoxForText($sWeek);

            $iX = $iOffsetLeft + ($iWidth-$oBoundingBox->getWidth())/2;
            $iY = $iY + ($iHeight+$oBoundingBox->getHeight())/2;

            $this->writeTextWithBorder($sWeek, $iX, $iY, $this->m_aColors['Week_Nr']
                , $iBorderSize, $this->m_aColors['Week_Nr_Border']
            );

            $oDate->add($oOneWeek);
        }#for
    }

/////////////////////////////// Drawing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return bool
     */
    protected function drawDecorations()
    {
        return $this->drawDecorationFunction($this->getDecorations(), 'drawDecoration');
    }

    /**
     * @return bool
     */
    protected function drawDecorationBackgrounds()
    {
        return $this->drawDecorationFunction($this->getDecorations(), 'drawDecorationBackground');
    }

    /**
     * @param array $p_aDecorations
     * @param $p_sFunction
     *
     * @return bool
     */
    protected function drawDecorationFunction(Array $p_aDecorations, $p_sFunction)
    {
        $bTotalResult = true;

        $cSort = function(Decoration $p_oA, Decoration $p_oB){

            if ($p_oA->getDuration() < $p_oB->getDuration()) {
                $bResult = 1;
            }
            else if ($p_oA->getDuration() > $p_oB->getDuration()) {
                $bResult = -1;
            }
            else {
                $bResult = 0;
            }

            return $bResult;
        };

        /* @NOTE: Decorations need to be sorted by duration, so the longest
         *        decorations are drawn first, otherwise we'll get decorations
         *        overlapping later on.
         */
        usort($p_aDecorations, $cSort);

        foreach ($p_aDecorations as $t_oDecoration)
        {
            /** @var $t_oDecoration Decoration */
            /** @noinspection PhpUndefinedMethodInspection Method format() is actually defined in  DateTime. Stupid IDE*/
            $sStartDate = $t_oDecoration->getStartDate()->format('Ymd');
            /** @noinspection PhpUndefinedMethodInspection Method format() is actually defined in  DateTime. Stupid IDE*/
            $sEndDate = $t_oDecoration->getEndDate()->format('Ymd');

            $aDateCoordinates = $this->getDateCoordinates();
            $aDates = array_keys($aDateCoordinates);
            $sFirstDate = array_shift($aDates);

            $bCall = false;
            if (isset($aDateCoordinates[$sStartDate])) {
                // Decoration start date is this month
                $bCall = true;
            }
            elseif($sStartDate < $sFirstDate && isset($aDateCoordinates[$sEndDate]))
            {
                // Decoration end date is this month, even if the start date is not
                $t_oDecoration->setStartDate(DateTime::createFromFormat('Ymd', $sFirstDate));
                $bCall = true;
            }
            else
            {
                // Decoration end and start date are not this month
            }#if

            if ($bCall === true) {
                $bCallResult = $this->$p_sFunction($t_oDecoration);
                $bTotalResult = ($bTotalResult && $bCallResult);
            }#if
        }#foreach

        return $bTotalResult;
    }

    /**
     * @param Decoration $p_oDecoration
     */
    protected function drawDecorationBackground (Decoration $p_oDecoration)
    {
        // Secular holidays and birthdays should not have a background
        if($this->m_bDebug === true
            || $p_oDecoration->getType() == DecorationType::NATIONAL_HOLIDAY
            || $p_oDecoration->getType() == DecorationType::SCHOOL_HOLIDAY
        )
        {

            $oDate = clone $p_oDecoration->getStartDate();

            $sColor = $this->m_aColors[$p_oDecoration->getType()->__toString()];

            while((int) $oDate->format('Ymd') < $p_oDecoration->getEndDate()->format('Ymd'))
            {
                $oDimensions = $this->getDimensionsForDate($oDate);

                $iX = $oDimensions->getLeftOffset() + ($oDimensions->getWidth()+$oDimensions->getLineWidth())  * $oDimensions->getRow();
                $iY = $oDimensions->getTopOffset()  + ($oDimensions->getHeight() + $oDimensions->getLineHeight())* $oDimensions->getColumn();

                $this->drawRectangleFilled($iX, $iY, $iX+$oDimensions->getWidth(), $iY+$oDimensions->getHeight(), $sColor);

                $oDate->add($this->m_oOneDay);
            }#while
        }#if
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @throws Exception
     *
     * @return bool|null
     */
    protected function drawDecoration(Decoration $p_oDecoration)
    {
        $uResult = null;

        if($p_oDecoration->getTitle() === '')
        {
            throw new Exception('Title not set for decoration.');
        }
        else
        {
            switch($p_oDecoration->getType())
            {
                case DecorationType::BIRTHDAY:
                    $uResult = $this->drawBirthdayDecoration($p_oDecoration);
                break;


                case DecorationType::NATIONAL_HOLIDAY:
                case DecorationType::SCHOOL_HOLIDAY:
                case DecorationType::SECULAR_HOLIDAY:
                    $uResult = $this->drawHolidayDecoration($p_oDecoration);
                break;
            }#switch
        }#if

        return $uResult;
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    protected function drawHolidayDecoration(Decoration $p_oDecoration)
    {
        $this->m_iFontSize = ceil($this->getWidth() / 40)+1; // = 45 pixels

        return $this->drawDecorationText($p_oDecoration);
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    protected function drawBirthdayDecoration(Decoration $p_oDecoration)
    {
        $this->m_iFontSize = ceil($this->getWidth()/43.85);// = 40 pixels

        $iDateWidth = ceil($this->getWidth() / 21.925); //@TODO: Calculate DateWidth
        //$iDateHeight = ceil($this->getWidth()/29.2333333333334);// Value taken from $this->writeDayNumbers()

        $iYOffset = (-DayBlockDimensions::getBlockHeight()) + $this->m_iFontSize;

        return $this->drawDecorationText($p_oDecoration, -$iDateWidth, $iDateWidth, $iYOffset);
    }

    /**
     * @param Decoration $p_oDecoration
     * @param float $p_dCorrection
     * @param int $p_iXOffset
     * @param int $p_iYOffset
     *
     * @return bool
     */
    protected function drawDecorationText(
          Decoration $p_oDecoration
        , $p_dCorrection=0.0
        , $p_iXOffset=0
        , $p_iYOffset=0
    )
    {
        //$bResult = false;

        $oDate = clone $p_oDecoration->getStartDate();

        $iBorderThickness = ceil($this->getWidth() / 584.66666666667); // 3 pixels

        $oBoundingBox = $this->getBoundingBoxForText($p_oDecoration->getTitle());
        $iTextWidth = $oBoundingBox->getWidth();

        $oDimensions = $this->getDimensionsForDate($p_oDecoration->getStartDate());
        $iDuration = $p_oDecoration->getDuration();
        /*
          @TODO: Take a decoration that spans more than one week into account

           Either the holiday is a single day or it is several days or weeks.
           In the latter case the holiday might be spread across several rows
           Extra logic will be needed for such cases to calculate how many days
           are on which row (week). The text should be written on the (first)
           row that has the most days.
        */
        if (7 - $oDimensions->getRow() < $iDuration) {
            // Decoration spans more than one week
            $iDuration = 7 - $oDimensions->getRow();
        }#if

        $iBoxWidth = DayBlockDimensions::getBlockWidth() * $iDuration;
        if ($p_dCorrection !== 0.0) {
            $iBoxWidth = $iBoxWidth + $p_dCorrection;
        }

        if ($iTextWidth > $iBoxWidth) {
            // Text is wider than the space it is supposed to occupy
            // so it needs to be shrunk
            $oScratchImage = $this->createScratchImageForDecoration(
                  $p_oDecoration
                , $oBoundingBox
                , $iBorderThickness
            );
        }
        else {
            // Text is not wide enough to fill the space it is supposed to occupy
            // so it needs to be stretched
//                && $iTextWidth <= DayBlockDimensions::getBlockWidth() * $iDuration
            $iKerning = ($iBoxWidth - $iTextWidth) / strlen($p_oDecoration->getTitle());
            if ($iKerning < 2) //@TODO: Replace hard-coded value for minimum-kerning with class field
            {
                $iKerning = 0;
            }#if
        }#if

        $bSuccess = true;
        while ((int) $oDate->format('Ymd') < $p_oDecoration->getEndDate()->format('Ymd')) {
            $oDimensions = $this->getDimensionsForDate($oDate);

            $iX = round(self::calculateXFromDimension($oDimensions) + $p_iXOffset);
            $iY = round(self::calculateYFromDimension($oDimensions) + $p_iYOffset);

            $sDecorationLocation = $oDimensions->getRow() . '.' . $oDimensions->getColumn();
            if(
                   in_array($sDecorationLocation, $this->m_aAppliedDecorations)
                && $p_oDecoration->getType() != DecorationType::BIRTHDAY    //@TODO: Remove this line once FIXME below is resolved.
            )
            {
                //@FIXME: This logic needs to be expanded to take decorations that are drawn from the top into account  BMP/2012/10/21
                //        Like Birthdays, which are now skipped to avoid problems.
                $aValues = array_count_values($this->m_aAppliedDecorations);
                $iY = $iY - ($oBoundingBox->getHeight() * $aValues[$sDecorationLocation]);
            }#if

            array_push($this->m_aAppliedDecorations, $sDecorationLocation);

            if ($oDate->format('Ymd') === $p_oDecoration->getStartDate()->format('Ymd'))
            {
                if (isset($oScratchImage))
                {
                    $iY = $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY();

                    $this->debug(null, $iX, $iY, $iBoxWidth, $oScratchImage->getHeight(), 'blue');

                    $bCopied = imagecopyresampled(
                          $this->m_rImage, $oScratchImage->getImageResource()
                        , $iX, $iY
                        , 0, 0
                        , $iBoxWidth, $oScratchImage->getHeight()
                        , $iTextWidth, $oBoundingBox->getHeight()
                    );

                    $bSuccess = ($bSuccess && $bCopied);
                }
                else
                {
                    if(!isset($iKerning))
                    {
                        $iKerning = 0;
                    }
                    else
                    {
                        $iX = $iX + ($iKerning / 2);
                    }

                    $this->debug(null, $iX, $iY+DayBlockDimensions::getBlockHeight()-$this->m_iFontSize, $iBoxWidth, $this->m_iFontSize, 'red');

                    $iY = $iY + $oDimensions->getHeight();
                    $this->writeTextWithBorder(
                        $p_oDecoration->getTitle()
                        , $iX, $iY
                        , $this->m_aColors['white']
                        , $iBorderThickness
                        , $this->m_aColors['black']
                        , $iKerning
                    );
                }#if
            }#if
            $oDate->add($this->m_oOneDay);
        }#while

        $bResult = $bSuccess;

        return $bResult;
    }

    /**
     * @param Decoration $p_oDecoration
     * @param BoundingBox $p_oBoundingBox
     * @param int $p_iBorderThickness
     *
     * @return \ScratchImage
     */
    protected function createScratchImageForDecoration (
          Decoration $p_oDecoration
        , BoundingBox $p_oBoundingBox
        , $p_iBorderThickness
    )
    {
        $oScratchImage = new ScratchImage($p_oBoundingBox);

        $oScratchImage->setFontSize($this->m_iFontSize);

        if ($p_oDecoration->getTitle() !== '') {
            $oScratchImage->writeTextWithBorder(
                $p_oDecoration->getTitle()
                , 0, $p_oBoundingBox->getHeight() - $p_oBoundingBox->getLowerRightY() - 1
                , $this->m_aColors['white']
                , $p_iBorderThickness
                , $this->m_aColors['black']
            );
        }#if

        return $oScratchImage;
    }

    protected function drawBase()
    {
        // Colour the weekends light gray
        /** @noinspection PhpUndefinedMethodInspection */
        $this->colorWeekends();

        // Draw the outline box
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawOutline();
        // The border outline consists 8 parts, 4 sides + 4 rounded corners

        // Draw the grid lines
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawGrid();
        // The grid is 7x6 with an extra half-height row at the top and bottom for the
        // day names.

        // Draw dividers for the week numbers
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawDividers();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->writeDayNames();
    }

/////////////////////////////// Calculate Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     */
    protected function calculateDateCoordinates(DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $iLeftOffset = DayBlockDimensions::getLeftOffset() + DayBlockDimensions::getLineWidth();
        $iTopOffset  = DayBlockDimensions::getTopOffset()  + DayBlockDimensions::getLineHeight();

        $iHeight = DayBlockDimensions::getBlockHeight()+DayBlockDimensions::getLineHeight();
        $iWidth  = DayBlockDimensions::getBlockWidth()+DayBlockDimensions::getLineWidth();

        for($t_iColumnCounter = 0; $t_iColumnCounter < 6; $t_iColumnCounter++)
        {
            for($t_iRowCounter = 0; $t_iRowCounter < 7; $t_iRowCounter++)
            {
                $oDate->add($this->m_oOneDay);

                $iX = $iLeftOffset + $iWidth * $t_iRowCounter;
                $iY = $iTopOffset + $iHeight * $t_iColumnCounter;

                $this->storeDateCoordinates(
                      $oDate
                    , $t_iColumnCounter, $t_iRowCounter
                    , $iX, $iY
                );
            }#for
        }#for
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    protected static function calculateXFromDimension(DayBlockDimensions $oDimensions)
    {
        return ($oDimensions->getWidth() + $oDimensions->getLineWidth())
            * $oDimensions->getRow()
            + $oDimensions->getLeftOffset()
        ;
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    protected static function calculateYFromDimension(DayBlockDimensions $oDimensions)
    {
        return ($oDimensions->getHeight() + $oDimensions->getLineHeight())
            * $oDimensions->getColumn()
            + $oDimensions->getTopOffset()
        ;
    }

///////////////////////////////// Debug Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param null $p_sMethodName
     */
    protected function debug($p_sMethodName=null)
    {
        if($this->m_bDebug === true)
        {
            $aTrace = debug_backtrace();

            if(!isset($p_sMethodName))
            {
                $aCaller = $aTrace[1];
                $sMethodName = $aCaller['function'];
            }
            else{
                $sMethodName = $p_sMethodName;
            }#if

            $iThickness = $this->getWidth()/350;

            switch($sMethodName)
            {
                case 'writeMonth':
                    $this->drawRectangle(
                        0, 0
                        , $this->getWidth()-$iThickness
                        , $this->getHeight()/10.80-$iThickness
                        , $this->m_aColors['cyan']
                        , $iThickness
                    );
                break;

                case 'writeDayNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];
                    //$oDate = $aTrace[0]['args'][3];

                    $this->drawRectangle(
                          $iX, $iY
                        , $iX+DayBlockDimensions::getBlockWidth()-$iThickness
                        , $iY+DayBlockDimensions::getBlockHeight()-$iThickness
                        , $this->m_aColors['magenta']
                        , $iThickness
                    );
//                    imagestring($this->m_rImage, 5
//                        , $iX
//                        , $iY
//                        , $oDate->format('D M')
//                        , $this->m_aColors['magenta']
//                    );
                break;

                case 'drawDecorationText':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];
                    $iWidth  = $aTrace[0]['args'][3];
                    $iHeight = $aTrace[0]['args'][4];
                    $sColor  = $aTrace[0]['args'][5];

                    $this->drawRectangle(
                          $iX, $iY
                        , $iX+$iWidth-$iThickness
                        , $iY+$iHeight-$iThickness
                        , $this->m_aColors[$sColor]
                        , $iThickness
                    );
                break;

                case 'writeWeekNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];

                    $this->drawRectangle(
                          $iX, $iY
                        , $iX+DayBlockDimensions::getBlockWidth()/2-$iThickness
                        , $iY+DayBlockDimensions::getBlockHeight()-$iThickness
                        , $this->m_aColors['yellow']
                        , $iThickness
                    );
                break;

                default:
                    parent::debug($sMethodName);
                break;
            }#switch
        }#if
    }

    /**
     * @param $p_sMessage
     */
    private function log($p_sMessage)
    {
        if($this->m_bDebug === true)
        {
            static $bLoaded;

            $aArguments = func_get_args();

            $sFile = LIBRARY_DIRECTORY . 'debug.calendar.log';

            if($bLoaded !== true){
                $bLoaded = true;
                file_put_contents($sFile,'# ==============================================================================');
            }

            file_put_contents($sFile, var_export($aArguments,true), FILE_APPEND);
        }
    }
}


#EOF
