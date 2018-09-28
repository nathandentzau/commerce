<?php

namespace Drupal\Tests\commerce_payment\Unit\Plugin\Commerce\CheckoutPane;

use Drupal\Tests\UnitTestCase;
use Drupal\commerce_price\Price;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentProcess;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;

/**
 * Covers Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentProcess
 * 
 * @group commerce
 */
class PaymentProcessTest extends UnitTestCase {

  /**
   * The checkout flow.
   * 
   * @var Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface
   */
  protected $checkoutFlow;

  /**
   * The entity type manager.
   * 
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   * 
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messengerInterface;

  /**
   * A commerce order.
   *
   * @var Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->checkoutFlow = $this->createMock(CheckoutFlowInterface::class);
    $this->entityTypeManager = $this->createMock(
      EntityTypeManagerInterface::class
    );
    $this->messengerInterface = $this->createMock(MessengerInterface::class);
    $this->order = $this->createMock(OrderInterface::class);
  }

  /**
   * Covers method ::isVisible()
   *
   * @dataProvider isVisibleDataProvider
   */
  public function testIsVisible($expected, $is_zero, $is_visible, $step_id) {
    $price = $this->createMock(Price::class);
    $price
      ->expects($this->once())
      ->method('isZero')
      ->willReturn($is_zero);

    $this->order
      ->expects($this->once())
      ->method('getTotalPrice')
      ->willReturn($price);

    $checkoutPane = $this->createMock(CheckoutPaneInterface::class);
    $checkoutPane
      ->expects($this->once())
      ->method('isVisible')
      ->willReturn($is_visible);
    $checkoutPane
      ->expects($this->once())
      ->method('getStepId')
      ->willReturn($step_id);

    $this->checkoutFlow
      ->expects($this->once())
      ->method('getPane')
      ->with('payment_information')
      ->willReturn($checkoutPane);

    $plugin = $this->createPaymentProcess();
    $plugin->setOrder($this->order);

    $this->assertEquals($expected, $plugin->isVisible());
  }

  /**
   * Provides a data provider for ::testIsVisible().
   * 
   * @return array
   *   The return value of this data provider is a multi-dimensional array.
   *   Where the values at indexes 0 - 3 are as follows:
   * 
   *   0: Expected method result
   *   1: The result of Price::isZero().
   *   2: The result of CheckoutPaneInterface::isVisible().
   *   3: The result of CheckoutPaneInterface::getStepId('payment_information').
   */
  public function isVisibleDataProvider() {
    return [
      [TRUE, FALSE, TRUE, 'something'],
      [FALSE, TRUE, TRUE, 'something'],
      [FALSE, FALSE, FALSE, 'something'],
      [FALSE, FALSE, TRUE, '_disabled'],
    ];
  }

  /**
   * Regression test for null total price in method ::isVisible().
   */
  public function testIsVisibleWithNullTotalPrice() {
    $this->order
      ->expects($this->once())
      ->method('getTotalPrice')
      ->willReturn(NULL);

    $plugin = $this->createPaymentProcess();
    $plugin->setOrder($this->order);

    $this->assertFalse($plugin->isVisible());
  }

  /**
   * Creates an instance of PaymentProcess.
   *
   * @return Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentProcess
   *   An instance of a payment process plugin.
   */
  protected function createPaymentProcess() {
    return new PaymentProcess(
      [],
      'commerce_payment',
      ['plugin' => 'commerce_payment'],
      $this->checkoutFlow,
      $this->entityTypeManager,
      $this->messenger
    );
  }
    
}