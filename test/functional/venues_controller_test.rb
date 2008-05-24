require File.dirname(__FILE__) + '/../test_helper'
require 'venues_controller'

# Re-raise errors caught by the controller.
class VenuesController; def rescue_action(e) raise e end; end

class VenuesControllerTest < Test::Unit::TestCase
  fixtures :venues

  def setup
    @controller = VenuesController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new

    @first_id = venues(:first).id
  end

  def test_index
    get :index
    assert_response :success
    assert_template 'list'
  end

  def test_list
    get :list

    assert_response :success
    assert_template 'list'

    assert_not_nil assigns(:venues)
  end

  def test_show
    get :show, :id => @first_id

    assert_response :success
    assert_template 'show'

    assert_not_nil assigns(:venue)
    assert assigns(:venue).valid?
  end

  def test_new
    get :new

    assert_response :success
    assert_template 'new'

    assert_not_nil assigns(:venue)
  end

  def test_create
    num_venues = Venue.count

    post :create, :venue => {}

    assert_response :redirect
    assert_redirected_to :action => 'list'

    assert_equal num_venues + 1, Venue.count
  end

  def test_edit
    get :edit, :id => @first_id

    assert_response :success
    assert_template 'edit'

    assert_not_nil assigns(:venue)
    assert assigns(:venue).valid?
  end

  def test_update
    post :update, :id => @first_id
    assert_response :redirect
    assert_redirected_to :action => 'show', :id => @first_id
  end

  def test_destroy
    assert_nothing_raised {
      Venue.find(@first_id)
    }

    post :destroy, :id => @first_id
    assert_response :redirect
    assert_redirected_to :action => 'list'

    assert_raise(ActiveRecord::RecordNotFound) {
      Venue.find(@first_id)
    }
  end
end
