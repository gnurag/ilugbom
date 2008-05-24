require File.dirname(__FILE__) + '/../test_helper'
require 'minutes_controller'

# Re-raise errors caught by the controller.
class MinutesController; def rescue_action(e) raise e end; end

class MinutesControllerTest < Test::Unit::TestCase
  fixtures :minutes

  def setup
    @controller = MinutesController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new

    @first_id = minutes(:first).id
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

    assert_not_nil assigns(:minutes)
  end

  def test_show
    get :show, :id => @first_id

    assert_response :success
    assert_template 'show'

    assert_not_nil assigns(:minute)
    assert assigns(:minute).valid?
  end

  def test_new
    get :new

    assert_response :success
    assert_template 'new'

    assert_not_nil assigns(:minute)
  end

  def test_create
    num_minutes = Minute.count

    post :create, :minute => {}

    assert_response :redirect
    assert_redirected_to :action => 'list'

    assert_equal num_minutes + 1, Minute.count
  end

  def test_edit
    get :edit, :id => @first_id

    assert_response :success
    assert_template 'edit'

    assert_not_nil assigns(:minute)
    assert assigns(:minute).valid?
  end

  def test_update
    post :update, :id => @first_id
    assert_response :redirect
    assert_redirected_to :action => 'show', :id => @first_id
  end

  def test_destroy
    assert_nothing_raised {
      Minute.find(@first_id)
    }

    post :destroy, :id => @first_id
    assert_response :redirect
    assert_redirected_to :action => 'list'

    assert_raise(ActiveRecord::RecordNotFound) {
      Minute.find(@first_id)
    }
  end
end
