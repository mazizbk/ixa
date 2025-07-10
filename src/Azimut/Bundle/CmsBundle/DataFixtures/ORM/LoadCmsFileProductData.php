<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 11:11:03
 */

namespace Azimut\Bundle\CmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\CmsBundle\Entity\CmsFileProduct;
use Azimut\Bundle\CmsBundle\Entity\ProductItem;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;

class LoadCmsFileProductData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $azimutProductTable = new CmsFileProduct();
        $azimutProductTable
            ->setTitle('Table tactile Azimut', 'fr')
            ->setTitle('Azimut touch table', 'en')
            ->setSubtitle("Borne interactive équipée d'un écran tactile de grande taille", 'fr')
            ->setText(<<<EOT
<p>Pensée autour d’un écran tactile 32 pouces dualtouch, la table tactile Azimut est conçue pour un usage intérieur.<br>
<br>
Son design moderne et épuré en inox lui permet de s’intégrer facilement dans son environnement (salons, hall d’entreprises…). Elle trouve très bien sa place au sein de lieux muséographiques (expositions, musées…).<br>
<br>
L’inclinaison de son écran tactile de grande taille procure un très haut confort de consultation quelle que soit la taille de l’usager (PMR, enfants…).<br>
<br>
Elle est idéale dans un contexte d'orientation en accueillant un plan interactif.<br>
<br>
</p>
<p><br>
<strong>Dimensions</strong><br>
Largeur 100 cm x profondeur 50 cm x&nbsp; hauteur 110cm</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-table'))
            )
        ;
        $manager->persist($azimutProductTable);


        $azimutProductEasyTouchUp = new CmsFileProduct();
        $azimutProductEasyTouchUp
            ->setTitle('Borne interactive Easy', 'fr')
            ->setTitle('Easy interactive kiosk', 'en')
            ->setSubtitle("Egalement disponible en format tablette", 'fr')
            ->setText(<<<EOT
<strong>La borne tactile EASY : Ultra compacte, Nomade et Novatrice !</strong>
<p><br>La borne interactive EASY allie <span style="font-style: italic; font-weight: bold;">praticité</span> et <span style="font-style: italic; font-weight: bold;">modernité</span> en intégrant :</p>
<ul>
<li>Un écran tactile 18 pouces format 16:9.</li>
<li>Un PC multimédia.</li>
</ul>
<p><br>Sa <span style="font-style: italic; font-weight: bold;">polyvalence</span> ainsi que son<span style="font-style: italic; font-weight: bold;"> adaptabilité</span> font de cette borne interactive un moyen de communication indispensable.<br>Entièrement tactile et légère (16 Kg seulement !), elle est idéale pour un usage nomade, évènementiel ou sédentaire. <br>Sa hauteur la rend <span style="font-style: italic; font-weight: bold;">accessible à tous</span> (enfants, adultes, personnes à mobilité réduite).</p>
<p>&nbsp;</p>
<p><span style="font-style: italic; font-weight: bold;">Dimensions</span><br>Hauteur 100 cm x largeur 50 cm x profondeur 40 cm</p>
<p style="font-style: italic; font-weight: bold;"><br>Options</p>
<p>Casques audio, module Wifi avec antenne orientable, lecteur de code barre, support pour usage mural.</p>
<p>&nbsp;</p>
<p><strong>Disponible en format tablette (10 pouces), et jusqu'à 21 pouces !</strong></p>
EOT
            , 'fr')
        ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-easy-touch-up'))
            )
        ;
        $manager->persist($azimutProductEasyTouchUp);


        $azimutProductTotemPlexy = new CmsFileProduct();
        $azimutProductTotemPlexy
            ->setTitle("Totem tactile / Totem d'information", 'fr')
            ->setTitle("Touch totem / information Totem", 'en')
            ->setSubtitle("Totem d'affichage ou à usage tactile : mode portrait ou paysage", 'fr')
            ->setText(<<<EOT
<p>Équipé d'un écran d'affichage passif ou d'un écran tactile, le totem offre une place privilégiée à votre information.</p>
<p>Son écran de grande taille en fait un atout dans la diffusion de votre information.<br><br>Entièrement sécurisé, ce totem est utilisé par nos clients dans des lieux très différents (hall d'accueil, galerie marchande...).</p>
<p>La hauteur de son écran le rend facilement utilisable et offre un accès simple aux personnes à mobilité réduite.<br><br>Ce totem peut héberger tous types d'application, d'un site Internet à une application multimédia dédiée (<a target="_blank" href="http://azimut.net/?titre=plan-interactif-sur-ecran-tactile&amp;mode=produit&amp;idFicheMere=43&amp;rubrique=affichage_dynamique&amp;id=676">plan interactif</a>...). L'utilisation du logiciel d'affichage dynamique Aziplayer vous permet de communiquer en temps réel sur votre actualité, celle de votre région, de diffuser des informations pratiques (horaires et lieux des conférences...)...<br><br><strong>Dimension</strong></p>
<p>Écran tactile : 40 pouces</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-totem-plexy'))
            )
        ;
        $manager->persist($azimutProductTotemPlexy);


        $azimutProductArbreCommunication = new CmsFileProduct();
        $azimutProductArbreCommunication
            ->setTitle("Arbre de communication", 'fr')
            ->setTitle("Communication tree", 'en')
            ->setSubtitle("Écran de grande taille pour usage passif ou tactile", 'fr')
            ->setText(<<<EOT
<p>Conçu autour d'un écran LCD 40 pouces tactile ou non, son design imposant et aérien met en évidence votre <span style="font-style: italic; font-weight: bold;">information</span>, qu’elle soit passive et/ou interactive. </p><p>L’Arbre de communication trouve naturellement sa place dans les lieux de passage (galerie marchande, hall, etc...).<br>
Lorsque personne ne l'utilise, l'écran tactile devient alors un support idéal de PLV numérique.<br>
<br>
<span style="font-style: italic; font-weight: bold;">Dimensions</span><br>
Hauteur 220 cm, surface occupée au sol 100x80 cm<br>
<br>
<span style="font-style: italic; font-weight: bold;">Options</span></p>Taille de l'écran au choix : 42, 46 ou 52 pouces, personnalisation (plexiglass transparent entre les 2 colonnes et signalétique adhésive), supports/ tablettes<br>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-arbre-communication'))
            )
        ;
        $manager->persist($azimutProductArbreCommunication);


        $azimutProductMuseo = new CmsFileProduct();
        $azimutProductMuseo
            ->setTitle("Borne interactive Muséo", 'fr')
            ->setTitle("Museo interactive kiosk", 'en')
            ->setSubtitle("Borne interactive tactile, compacte et élégante", 'fr')
            ->setText(<<<EOT
<p>Le design de cette borne interactive la rend particulièrement adaptée à la consultation d’applications multimédia dans tout <em><strong>espace grand public.</strong></em></p><p><em><strong><br>
</strong></em></p><p>Équipée d'un écran 19 pouces tactile, la borne interactive Muséo offre un<em><strong> grand confort</strong></em> d’utilisation.</p><p>Dotée de haut-parleurs, elle peut recevoir en option un ou plusieurs casques audio assurant ainsi un confort d’écoute optimal.</p><p><br>
</p><p>De plus, sa façade avant est <em><strong>personnalisable</strong></em> par une signalétique adhésive. La borne tactile Muséo se veut à votre image.</p><p>Auto-stable, elle pourra également être fixée au sol.<br>
<br>
<em><strong>Dimensions </strong></em><br>
H 110cm x l 50cm x P 45cm</p><p><br>
</p><p><em><strong>Options</strong></em><br>
Casques audio, lecteur RFID</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-museo'))
            )
        ;
        $manager->persist($azimutProductMuseo);


        $azimutProductElite = new CmsFileProduct();
        $azimutProductElite
            ->setTitle("Borne tactile E-Lite", 'fr')
            ->setTitle("E-Lite tactile kiosk", 'en')
            ->setSubtitle("", 'fr')
            ->setText(<<<EOT
<p>La borne tactile E-lite est spécialement conçue pour répondre à une problématique claire : offrir un juste équilibre entre prix et performance.<br>
La borne tactile E-lite est un <em>modèle intérieur</em> facilement installable dans des magasins, ou lieux à fort trafic, de par son <em>faible encombrement</em> au sol et sa hauteur d'1m40.<br>
<br>
Son écran 17 pouces offre une <em>très bonne lisibilité</em> du contenu tout en assurant une confidentialité certaine lors de la saisie d’informations.<br>
A l'image de l'ensemble de notre gamme de bornes interactives, la borne tactile E-Lite est entièrement sécurisée. De plus, elle est personnalisable par sa couleur mais également par l’ajout d’une signalétique adhésive sur sa large façade avant.<br>
<br>
<em><strong>Dimensions</strong></em><br>
Hauteur 140 cm x largeur 45 cm x profondeur 8 cm.</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-elite'))
            )
        ;
        $manager->persist($azimutProductElite);


        $azimutProductDesign = new CmsFileProduct();
        $azimutProductDesign
            ->setTitle("Borne interactive Design", 'fr')
            ->setTitle("Design interactive kiosk", 'en')
            ->setSubtitle("Borne tactile à usage debout", 'fr')
            ->setText(<<<EOT
<p>La ligne élancée de cette borne interactive en fait un modèle sobre et esthétique. <span style="font-weight: bold; font-style: italic;">Design</span>, elle est réservée aux espaces intérieurs semi-protégés, comme les halls d’accueil.</p>
<p>&nbsp;</p>
<p>Ce modèle de borne tactile, équipée d'un écran 19 pouces, offre un <span style="font-weight: bold; font-style: italic;">grand confort</span> de consultation aux usagers. Sa façade avant peut être <span style="font-weight: bold; font-style: italic;">personnalisée</span> et accueillir une signalétique adhésive parfaitement adaptée à vos besoins et envies.<br>Auto-stable de nature, elle peut également être fixée au sol.<br><br><span style="font-weight: bold; font-style: italic;">Dimensions</span><span style="text-decoration: underline;"><br></span>Largeur 50 cm x profondeur 40 cm x hauteur 155 cm<br><br><span style="font-weight: bold; font-style: italic;">Options</span></p>
<ul>
<li>Pupitre avec clavier antivandalisme inox</li>
<li>Trackball rétro-éclairé bleu</li>
<li>Autres : casques audio, lecteur RFID, lecteur de code-barre, tablette</li>
</ul>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-design'))
            )
        ;
        $manager->persist($azimutProductDesign);


        $azimutProductTotem = new CmsFileProduct();
        $azimutProductTotem
            ->setTitle("Totem tactile", 'fr')
            ->setTitle("Tactile totem", 'en')
            ->setSubtitle("Votre solution tactile grand format", 'fr')
            ->setText(<<<EOT
<p>Innovant et attractif, ce totem <strong>semi outdoor</strong> permet de consulter de grands visuels, tels que les plans d'orientation, pour le plus grand confort des utilisateurs.</p>
<p>&nbsp;</p>
<p>Son écran 46 pouces, disposé en <strong>mode portrait</strong>, en fait un atout dans la diffusion de votre information.</p>
<p>&nbsp;</p>
<p>La hauteur de son écran le rend facilement utilisable tout en offrant une grande visibilité du contenu diffusé.</p>
<p>&nbsp;</p>
<p>Le rétro éclairage du point "I" rend le totem facilement repérable de loin, notamment dans des lieux à fort trafic (hall de gare, galerie marchande...)</p>
<p>&nbsp;</p>
<p>Ce totem peut héberger tous types d'application, d'un site internet à une application multimédia dédiée.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><em><strong>Dimensions (en cm)</strong></em></p>
<p>L 100 X P 8 X H 250</p>
<p>&nbsp;</p>
<p><em><strong>Option</strong> </em></p>
<p>Signalétique adhésive</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-totem'))
            )
        ;
        $manager->persist($azimutProductTotem);


        $azimutProductVentana = new CmsFileProduct();
        $azimutProductVentana
            ->setTitle("Borne interactive extérieure murale Ventana", 'fr')
            ->setTitle("Outdoor onwall kiosk Ventana", 'en')
            ->setSubtitle("Point d'information tactile extérieur pour une consultation 24h/24", 'fr')
            ->setText(<<<EOT
<p>La borne tactile VENTANA est un modèle mural d’extérieur, sécurisé et accessible 24h/24.</p><p>Entièrement <strong><em>sécurisée</em></strong>, elle est étudiée pour résister au vandalisme. De plus sa forme compacte alliée à son écran 19 pouces tactile offre un fort confort de consultation.</p><p>L'écran tactile de haute luminosité lui permet d’être lisible en toutes circonstances.<br>
Son système de régulation de température et protection électrique intégrés lui confère une <em><strong>haute résistance aux intempéries</strong></em>.</p><p>Cette borne interactive se fixe très facilement au mur. En effet, aucun percement ni travaux lourds ne sont nécessaires à sa mise en œuvre.<br>
Son ergonomie intuitive lui permet de répondre à de nombreux usages. Elle trouve ainsi facilement sa place au sein de capitaineries, offices de tourisme, cimetières...<br>
<em><br>
<strong>Dimensions</strong></em></p>Hauteur 80cm x largeur 55cm x profondeur 14cm. <br>
<p><strong><br>
<em>Option</em></strong></p>Clavier inox antivandalisme<p><br>
</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-ventana'))
            )
        ;
        $manager->persist($azimutProductVentana);


        $azimutProductUrbana = new CmsFileProduct();
        $azimutProductUrbana
            ->setTitle("Totem tactile d'extérieur Urbana", 'fr')
            ->setTitle("Outdoor tactile totem Urbana", 'en')
            ->setSubtitle("Borne interactive extérieure", 'fr')
            ->setText(<<<EOT
<p>La borne tactile Urbana est un modèle d'extérieur pour un <span style="font-style: italic; font-weight: bold;">usage 24h/24</span>. Elle est adaptée aux lieux publics extérieurs. Scellée dans le sol, elle est étudiée pour résister au vandalisme et aux intempéries, tout en arborant un <span style="font-style: italic; font-weight: bold;">design </span>aux lignes fluides et élancées.<br>
Ce modèle de borne interactive est équipé d'un dispositif de thermorégulation, lui permettant de résister à des températures extrêmes.<br>
Son écran tactile de 19 pouces est spécialement conçu pour un environnement urbain.<br>
De plus, l'ajout d'un trackball inox antivandalisme permet de rendre cette borne interactive accessible à tous (PMR, enfants...).<br>
<br>
<span style="font-style: italic; font-weight: bold;">Dimensions</span><br>
<br>
Largeur 60 cm x profondeur 40 cm x hauteur 220 cm</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-urbana'))
            )
        ;
        $manager->persist($azimutProductUrbana);


        $azimutProductTouchscreen = new CmsFileProduct();
        $azimutProductTouchscreen
            ->setTitle("Ecrans tactiles", 'fr')
            ->setTitle("Touchscreens", 'en')
            ->setSubtitle("Longtemps réservés aux bornes interactives, les écrans tactiles s'agrandissent.", 'fr')
            ->setText(<<<EOT
<p>Véritable vecteur de dynamisme, l'ajout d'interactivité, par le biais d'un écran tactile, anime votre information.<br>
Ludique et facile à manipuler, l'écran tactile permet d'impliquer fortement l'utilisateur, le rendant ainsi acteur de son information et donc réceptif à votre communication.</p><p></p><p>Dans l'optique de mettre en valeur votre information, nous vous proposons un large choix de taille d'écrans, du 15 pouces au 52 pouces.</p><p><br>
Sa grande polyvalence dans les usages vous permet de diffuser différents contenus :</p><ul><li>Plan d'orientation interactif</li><li>Visite virtuelle</li><li>Quizz</li><li>Image panoramique 360°</li><li>Navigation Internet</li><li>Contenus multimédia spécifiques</li><li>...</li></ul><p>Pour rendre l'écran tactile encore plus visible, un habillage sur-mesure peut être réalisé. Divers matériaux sont utilisés:</p><ul><li>Plexiglass</li><li>Inox brossé</li><li>Signalétique adhésive</li></ul>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-touchscreen'))
            )
        ;
        $manager->persist($azimutProductTouchscreen);


        $azimutProductKiosqueUniversel = new CmsFileProduct();
        $azimutProductKiosqueUniversel
            ->setTitle("Kiosque universel", 'fr')
            ->setTitle("Kiosque universel", 'en')
            ->setSubtitle("Le point d'information et de billetterie pour les activités culturelles, de loisirs et touristiques", 'fr')
            ->setText(<<<EOT
<p>Cette borne interactive offre une alternative aux sites Internet de réservation en ligne, et aux guichets traditionnels et leurs limites (horaires, attentes, distance …).<br>
<br>
Équipé d’un écran tactile 17 pouces, le Kiosque universel intègre également un TPE, permettant l’achat, à tout moment et en tout lieu, de billets. Suite au paiement, le Kiosque universel imprime immédiatement le billet et le restitue à l’acheteur.</p><p>Le Kiosque Universel est plus qu’une borne interactive ... En effet, ce dernier, en plus de permettre l’achat de billets, s’avère être un véritable outil pour votre communication.</p><p>Son écran additionnel, équipé du logiciel Aziplayer, facilite la diffusion de l’information en temps réel.</p><p>Cette borne interactive est ainsi visible de loin et dispose d’un outil de communication très large, pouvant ainsi assurer la promotion des activités disponibles sur la borne.<br>
<br>
Ses objectifs principaux :</p><ul><li>Faire coïncider l’offre avec les publics potentiels</li><li>Être utilisé comme un espace d’achat, de promotion et de communication.</li></ul><p><br>
</p>
EOT
            , 'fr')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec nisl eget diam placerat euismod. Nunc venenatis varius purus id luctus. Fusce et justo quis ipsum dignissim commodo. Aliquam urna dolor, tempor nec ultricies vitae, tempor a magna. Quisque luctus pretium nunc, id finibus ipsum faucibus non. Sed quis risus at nibh facilisis tempus. Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?
', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-kiosks-kiosque-universel'))
            )
        ;
        $manager->persist($azimutProductKiosqueUniversel);


        $productShoes = new CmsFileProduct();
        $productShoes
            ->setTitle("Chaussures Démo", 'fr')
            ->setTitle("Demo shoes", 'en')
            ->setSubtitle("Lorem ipsum dolor sit amet", 'fr')
            ->setText('<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', 'fr')
            ->setText('<p>Suspendisse rhoncus eros quis mi scelerisque egestas. Suspendisse blandit fringilla mi, nec molestie nunc lacinia quis. Integer dictum commodo rutrum. Nulla facilisi. Morbi accumsan tristique nulla, quis pellentesque est sodales et?</p>', 'en')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-shoes'))
            )
            ->setPrice(6025)
        ;
        $manager->persist($productShoes);
        $productShoesItem1 = new ProductItem();
        $productShoesItem1
            ->setName('Size 7', 'en')
            ->setName('Pointure 39', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem2 = new ProductItem();
        $productShoesItem2
            ->setName('Size 7.5', 'en')
            ->setName('Pointure 40', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem3 = new ProductItem();
        $productShoesItem3
            ->setName('Size 8', 'en')
            ->setName('Pointure 41', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem4 = new ProductItem();
        $productShoesItem4
            ->setName('Size 9', 'en')
            ->setName('Pointure 42', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem5 = new ProductItem();
        $productShoesItem5
            ->setName('Size 10', 'en')
            ->setName('Pointure 43', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem6 = new ProductItem();
        $productShoesItem6
            ->setName('Size 10.5', 'en')
            ->setName('Pointure 44', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem7 = new ProductItem();
        $productShoesItem7
            ->setName('Size 11', 'en')
            ->setName('Pointure 45', 'fr')
            ->setCmsFile($productShoes)
        ;
        $productShoesItem8 = new ProductItem();
        $productShoesItem8
            ->setName('Size 12', 'en')
            ->setName('Pointure 46', 'fr')
            ->setCmsFile($productShoes)
        ;


        $manager->flush();

        $this->addReference('cms-product-kiosk-table', $azimutProductTable);
        $this->addReference('cms-product-kiosk-easy-touch-up', $azimutProductEasyTouchUp);
        $this->addReference('cms-product-kiosk-arbre-communication', $azimutProductArbreCommunication);
        $this->addReference('cms-product-kiosk-totem-plexy', $azimutProductTotemPlexy);
        $this->addReference('cms-product-kiosk-museo', $azimutProductMuseo);
        $this->addReference('cms-product-kiosk-elite', $azimutProductElite);
        $this->addReference('cms-product-kiosk-design', $azimutProductDesign);
        $this->addReference('cms-product-kiosk-totem', $azimutProductTotem);
        $this->addReference('cms-product-kiosk-ventana', $azimutProductVentana);
        $this->addReference('cms-product-kiosk-urbana', $azimutProductUrbana);
        $this->addReference('cms-product-kiosk-touchscreen', $azimutProductTouchscreen);
        $this->addReference('cms-product-kiosk-kiosque-universel', $azimutProductKiosqueUniversel);
        $this->addReference('cms-product-shoes', $productShoes);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // order in witch files are loaded
    }
}
