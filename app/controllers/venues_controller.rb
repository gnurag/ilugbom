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
    @venue_pages, @venues = paginate :venues, :conditions => published_sql(self.controller_name), :order => "venues.created_at, venues.id DESC",:per_page => 10
    @page_title = "Venues"
  end

  def show
    @venue = Venue.find(params[:id], :conditions => published_sql(self.controller_name))
    @recent_venues = Venue.find(:all, :conditions => published_sql(self.controller_name), :order => "venues.created_at, venues.id DESC", :limit => "10")
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
