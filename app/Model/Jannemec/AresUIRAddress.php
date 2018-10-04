<?php
namespace jannemec;
/**
 * Adresa UIR standard ze souboru uvis_datatypes_v_1.0.3
 *	<xsd:complexType name="adresa_UIR">
 *		<xsd:annotation>
 *			<xsd:documentation xml:lang="cs">Definice elementu obecné adresy </xsd:documentation>
 *		</xsd:annotation>
 *		<xsd:sequence>
 *			<xsd:element name="KOL" type="kod_oblasti" minOccurs="0"/>
 *			<xsd:element name="KK" type="kod_kraje" minOccurs="0"/>
 *			<xsd:element name="KOK" type="kod_okresu" minOccurs="0"/>
 *			<xsd:element name="KO" type="kod_obce" minOccurs="0"/>
 *			<xsd:element name="KPO" type="kod_pobvod" minOccurs="0"/>
 *			<xsd:element name="KSO" type="kod_sobvod" minOccurs="0"/>
 *			<xsd:element name="KN" type="kod_nobvod" minOccurs="0"/>
 *			<xsd:element name="KCO" type="kod_casti_obce" minOccurs="0"/>
 *			<xsd:element name="KMC" type="kod_mestske_casti" minOccurs="0"/>
 *			<xsd:element name="PSC" type="psc" minOccurs="0"/>
 *			<xsd:element name="KUL" type="kod_ulice" minOccurs="0"/>
 *			<xsd:element name="CD" type="cis_dom" minOccurs="0"/>
 *			<xsd:element name="TCD" type="typ_cis_dom" minOccurs="0"/>
 *			<xsd:element name="CO" type="cis_or" minOccurs="0"/>
 *			<xsd:element name="PCO" type="pism_cislo_orientacni" minOccurs="0"/>
 *			<xsd:element name="KA" type="kod_adresy" minOccurs="0"/>
 *			<xsd:element name="KOB" type="kod_objektu" minOccurs="0"/>
 *			<xsd:element name="PCD" type="pcd" minOccurs="0"/>
 *		</xsd:sequence>
 *	</xsd:complexType>
 *
 *
 * @author nemec
 */
class AresUIRAddress {
    use \Nette\SmartObject;
    /**
     * kód oblasti
     *			<xsd:element name="KOL" type="kod_oblasti" minOccurs="0"/>
     * @var String
     */
    private $kol = null;
    /**
     * kód kraje
     *			<xsd:element name="KK" type="kod_kraje" minOccurs="0"/>
     * @var String
     */
    private $kk = null;
    /**
     * kód okresu
     *			<xsd:element name="KOK" type="kod_okresu" minOccurs="0"/>
     * @var String
     */
    private $kok = null;
    /**
     * kód obce
     *			<xsd:element name="KO" type="kod_obce" minOccurs="0"/>
     * @var String
     */
    private $ko = null;
    /**
     * kód podbvod
     *			<xsd:element name="KPO" type="kod_pobvod" minOccurs="0"/>
     * @var String
     */
    private $kpo = null;
    /**
     * kód sobvod
     *			<xsd:element name="KSO" type="kod_sobvod" minOccurs="0"/>
     * @var String
     */
    private $kso = null;
    /**
     * kód nobvod
     *			<xsd:element name="KN" type="kod_nobvod" minOccurs="0"/>
     * @var String
     */
    private $kn = null;
    /**
     * kód části obce
     *			<xsd:element name="KCO" type="kod_casti_obce" minOccurs="0"/>
     * @var String
     */
    private $kco = null;
    /**
     * kód městské části
     *			<xsd:element name="KMC" type="kod_mestske_casti" minOccurs="0"/>
     * @var String
     */
    private $kmc = null;
    /**
     * PSČ
     *			<xsd:element name="PSC" type="psc" minOccurs="0"/>
     * @var String
     */
    private $psc = null;
    /**
     * kód ulice
     *			<xsd:element name="KUL" type="kod_ulice" minOccurs="0"/>
     * @var String
     */
    private $kul = null;
    /**
     * číslo domovní
     *			<xsd:element name="CD" type="cis_dom" minOccurs="0"/>
     * @var String
     */
    private $cd = null;
    /**
     * typ čísla domovního
     *			<xsd:element name="TCD" type="typ_cis_dom" minOccurs="0"/>
     * @var String
     */
    private $tcd = null;
    /**
     * Číslo orientační
     *			<xsd:element name="CO" type="cis_or" minOccurs="0"/>
     * @var String
     */
    private $co = null;
    /**
     * Písmeno čísla orientačního
     *			<xsd:element name="PCO" type="pism_cislo_orientacni" minOccurs="0"/>
     * @var String
     */
    private $pco = null;
    /**
     * kód adresy
     *			<xsd:element name="KA" type="kod_adresy" minOccurs="0"/>
     * @var String
     */
    private $ka = null;
    /**
     * kód objektu
     *			<xsd:element name="KOB" type="kod_objektu" minOccurs="0"/>
     * @var String
     */
    private $kob = null;
    /**
     * pcd
     *			<xsd:element name="PCD" type="pcd" minOccurs="0"/>
     * @var String
     */
    private $pcd = null;

}