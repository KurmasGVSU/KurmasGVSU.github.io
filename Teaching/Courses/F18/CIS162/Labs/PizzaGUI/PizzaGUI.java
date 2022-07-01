import java.awt.*;
import javax.swing.*;
import java.text.*;

/*******************************************************
 * Demonstration of GUI components and Action Listeners
 * using a pizza order form
 *
 * @author Scott Grissom (modified by Zachary Kurmas)
 * @version October 15, 2018
 ******************************************************/
public class PizzaGUI {

  private JFrame frame;

  // FIX ME: add additional radio buttons for sizes and crust options
  private JRadioButton smallRadio, mediumRadio;
  private JRadioButton thinRadio, thickRadio;

  // FIX ME: add additional check boxes for meats and veggies
  private JCheckBox sausageCheck, pepperoniCheck;
  private JCheckBox peppersCheck, onionsCheck;

  private JLabel priceLbl;
  private JButton priceBtn;
  private JButton orderBtn;
  private PizzaOrder theOrder;

  /**
   * menu items to display about and to quit
   */
  JMenuItem quitItem;
  JMenuItem aboutItem;

  /****************************************************
   Constructor
   ****************************************************/
  public PizzaGUI() {

    // FIX ME: create your own title
    frame = new JFrame("GVSU Pizza");

    // FIX ME: Instantiate the PizzaOrder object

    // hide away details of creating menus
    setupMenus();

    // set layout manager and fonts
    frame.setLayout(new GridBagLayout());
    GridBagConstraints position = new GridBagConstraints();
    Font myFont = new Font("serif", Font.BOLD, 20);

    // make components left justified with padding on right
    position.anchor = GridBagConstraints.LINE_START;
    position.insets = new Insets(0, 0, 00, 20);

    // create radio buttons for size
    smallRadio = new JRadioButton("Small", true);
    mediumRadio = new JRadioButton("Medium");

    // combine radio buttons in a group
    ButtonGroup sizesGroup = new ButtonGroup();
    sizesGroup.add(smallRadio);
    sizesGroup.add(mediumRadio);

    // Place radio buttons for size
    JLabel sizeLbl = new JLabel("Pick Size");
    sizeLbl.setFont(myFont);
    position.gridy = 0;
    position.gridx = 1;
    frame.add(sizeLbl, position);
    position.gridy = 1;
    frame.add(smallRadio, position);
    position.gridy = 2;
    frame.add(mediumRadio, position);

    // Create radio buttons for crust
    thinRadio = new JRadioButton("Thin", true);
    thickRadio = new JRadioButton("Thick");

    // combine radio buttons in a group
    ButtonGroup crustGroup = new ButtonGroup();
    crustGroup.add(thinRadio);
    crustGroup.add(thickRadio);

    // Place radio buttons for crust
    JLabel crustLbl = new JLabel("Pick Crust");
    crustLbl.setFont(myFont);
    position.gridy = 0;
    position.gridx = 2;
    frame.add(crustLbl, position);
    position.gridy = 1;
    frame.add(thinRadio, position);
    position.gridy = 2;
    frame.add(thickRadio, position);

    // Create checkboxes for meat toppings
    sausageCheck = new JCheckBox("Sausage");
    pepperoniCheck = new JCheckBox("Pepperoni");

    // Place checkboxes for meat
    JLabel meatLbl = new JLabel("Pick Meat");
    meatLbl.setFont(myFont);
    position.gridy = 0;
    position.gridx = 3;
    frame.add(meatLbl, position);
    position.gridy = 1;
    frame.add(sausageCheck, position);
    position.gridy = 2;
    frame.add(pepperoniCheck, position);

    // create checkboxes for veggie choices
    onionsCheck = new JCheckBox("Onions");
    peppersCheck = new JCheckBox("Peppers");

    // display checkboxes for veggies
    JLabel vegLbl = new JLabel("Pick Veggie");
    vegLbl.setFont(myFont);
    position.gridy = 0;
    position.gridx = 4;
    frame.add(vegLbl, position);
    position.gridy = 1;
    frame.add(peppersCheck, position);
    position.gridy = 2;
    frame.add(onionsCheck, position);

    // display price
    JLabel priceTitle = new JLabel("Price");
    position.gridy = 0;
    position.gridx = 5;
    priceTitle.setFont(myFont);
    frame.add(priceTitle, position);
    priceLbl = new JLabel("$0.00");
    priceLbl.setFont(myFont);
    position.gridy = 1;
    frame.add(priceLbl, position);

    // create and display the Price button
    priceBtn = new JButton("Price");
    position.gridx = 5;
    position.gridy = 2;
    frame.add(priceBtn, position);
    priceBtn.addActionListener(e-> priceButtonPushed());

    // FIX ME: create and display the Order button


    // display an image of pizza
    // FIX ME: update the file name
    ImageIcon icon = new ImageIcon("pizz.jpg");
    JLabel imageLabel = new JLabel(icon);
    position.gridy = 0;
    position.gridx = 0;
    position.gridheight = 10;
    frame.add(imageLabel, position);

    frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
    frame.pack();
    frame.setVisible(true);
  }

  /************************************************************
   Hide details of updating all order options
   ************************************************************/
  private void updatePizzaOrder() {

    theOrder.clearOrder();

    if (smallRadio.isSelected()) {
      theOrder.setSize(PizzaOrder.SMALL);
    }

    // FIX ME: add if statements for other sizes


    if (thinRadio.isSelected()) {
      theOrder.setCrust(PizzaOrder.THIN);
    }

    // FIX ME: add if statements for other crusts

    // FIX ME: add if statements for each meat option
    if (pepperoniCheck.isSelected()) {
      theOrder.addMeat();
    }

    // FIX ME: add if statements for each veggie option
    if (onionsCheck.isSelected()) {
      theOrder.addVeggie();
    }

  }

  /************************************************************
   Setup the file menu with two options: about and quit
   Menu items must register their action listeners
   ************************************************************/
  private void setupMenus() {

    // create and display the menu bar
    JMenuBar menusBar = new JMenuBar();
    frame.setJMenuBar(menusBar);

    // create a menu and add to the menubar
    JMenu fileMenu = new JMenu("File");
    menusBar.add(fileMenu);

    // FIX ME: create a menu item for About (see below)

    // create a menu item for Quit
    quitItem = new JMenuItem("Quit");
    fileMenu.add(quitItem);
    quitItem.addActionListener(e -> quit());
  }

  private void quit() {
    // FIX ME:
    // quit
  }

  private void priceButtonPushed() {
    // review order selections
    updatePizzaOrder();

    // display updated price
    NumberFormat fmt = NumberFormat.getCurrencyInstance();
    priceLbl.setText(fmt.format(theOrder.getPrice()));

  }

  /*****************************************************
   This is a standard main method that creates and displays
   the GUI.
   ****************************************************/
  public static void main(String args[]) {
    new PizzaGUI();
  }

}
