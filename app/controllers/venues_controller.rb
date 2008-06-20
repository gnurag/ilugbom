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

class VenuesController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def list
    @venue_pages, @venues = paginate :venues, :conditions => published_sql(self.controller_name), :order => "venues.created_at DESC",:per_page => 10
    @page_title = "Venues"
  end

  def show
    @venue = Venue.find(params[:id], :conditions => published_sql(self.controller_name))
    @recent_venues = Venue.find(:all, :conditions => published_sql(self.controller_name), :order => "venues.created_at DESC", :limit => "10")
    @page_title = @venue.name if @venue
  end

  def new
    @venue = Venue.new
  end

  def create
    @venue = Venue.new(params[:venue])
    @venue.urlpath = @venue.name.downcase.gsub(" ", "-")
    if @venue.save
      flash[:notice] = 'Venue was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @venue = Venue.find(params[:id])
  end

  def update
    @venue = Venue.find(params[:id])
    if @venue.update_attributes(params[:venue])
      @venue.urlpath = @venue.name.downcase.gsub(" ", "-")
      @venue.save
      flash[:notice] = 'Venue was successfully updated.'
      redirect_to :action => 'show', :id => @venue
    else
      render :action => 'edit'
    end
  end

  def destroy
    Venue.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
