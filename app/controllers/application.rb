# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  # Pick a unique cookie name to distinguish our session data from others'
  #session :session_key => '_ilugbom_session_id'  
  session :disabled => true

  def login_required
    redirect_to :controller => 'people', :action => 'login', :return => request.host_with_port+request.request_uri if not @current_user
  end
end
