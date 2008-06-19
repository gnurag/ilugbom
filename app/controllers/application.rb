#  Copyright (c) 2008, Anurag Patel <anurag@xinh.org>
#  All rights reserved.
#
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions are met:
#      * Redistributions of source code must retain the above copyright
#        notice, this list of conditions and the following disclaimer.
#      * Redistributions in binary form must reproduce the above copyright
#        notice, this list of conditions and the following disclaimer in the
#        documentation and/or other materials provided with the distribution.
#      * Neither the name of the Xinh Associates nor the
#        names of its contributors may be used to endorse or promote products
#        derived from this software without specific prior written permission.
#
#  THIS SOFTWARE IS PROVIDED BY ANURAG PATEL ``AS IS'' AND ANY
#  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
#  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
#  DISCLAIMED. IN NO EVENT SHALL ANURAG PATEL BE LIABLE FOR ANY
#  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
#  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
#  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
#  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
#  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
#  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  # Pick a unique cookie name to distinguish our session data from others'
  #session :session_key => '_ilugbom_session_id'  
  include AuthSystem
  require 'openssl'
  session :disabled => true
  prepend_before_filter :get_user_from_cookie, :init_site, :balancer_cookie

  def login_required
    redirect_to :controller => 'people', :action => 'login', :return => request.host_with_port+request.request_uri if not @current_user
  end

  def get_user_from_cookie
    user = get_user_from_login_cookie
    if not user.nil?
      ua = user.split("|")  # ua = user array
      @current_user = {
        :id => ua[0],
        :username => ua[1],
        :fullname => ua[2],
        :nickname => ua[3],
        :irc_nick => ua[4],
        :email => ua[5],
        :webpage => ua[6],
        :flickr_username => ua[7],
        :yahooim_username => ua[8],
        :gtalk_username => ua[9],
        :visible  => ua[10]
      }
    end
  end

  def init_site
    @nav_pages = Page.find(:all, :conditions => "pages.published = 1 AND pages.order_by >= 0", :order => "pages.order_by, pages.id ASC")
  end

  ## Stiky cookies
  # Setting a balancer cookie, so that Apache's mod_balancer module can reroute the requests to the correct 
  # mongrel server. 
  def balancer_cookie
    cookies[:BALANCEID] = 'balancer.mongrel' + (Time.now.sec % MONGREL_COUNT).to_s if !cookies[:BALANCEID]
    return true
  end

  private
  def published_sql(tablename="", fieldname = "published", extra_sql = "")
    conditions = @current_user ? " 1=1 " : "#{tablename}.#{fieldname} = 1"
    conditions += extra_sql
  end
  
end
